<?php
/**
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 */

require_once('AbstractController.php');

use Pagantis\OrdersApiClient\Client as PagantisClient;
use Pagantis\OrdersApiClient\Model\Order as PagantisModelOrder;
use Pagantis\ModuleUtils\Exception\AmountMismatchException;
use Pagantis\ModuleUtils\Exception\ConcurrencyException;
use Pagantis\ModuleUtils\Exception\MerchantOrderNotFoundException;
use Pagantis\ModuleUtils\Exception\NoIdentificationException;
use Pagantis\ModuleUtils\Exception\OrderNotFoundException;
use Pagantis\ModuleUtils\Exception\QuoteNotFoundException;
use Pagantis\ModuleUtils\Exception\ConfigurationNotFoundException;
use Pagantis\ModuleUtils\Exception\UnknownException;
use Pagantis\ModuleUtils\Exception\WrongStatusException;
use Pagantis\ModuleUtils\Model\Response\JsonSuccessResponse;
use Pagantis\ModuleUtils\Model\Response\JsonExceptionResponse;

/**
 * Class PagantisNotifyModuleFrontController
 */
class PagantisNotifyModuleFrontController extends AbstractController
{
    /**
     * @var bool $processError
     */
    protected $processError;

    /**
     * @var string $merchantOrderId
     */
    protected $merchantOrderId;

    /**
     * @var \Cart $merchantOrder
     */
    protected $merchantOrder;

    /**
     * @var string $pagantisOrderId
     */
    protected $pagantisOrderId;

    /**
     * @var \Pagantis\OrdersApiClient\Model\Order $pagantisOrder
     */
    protected $pagantisOrder;

    /**
     * @var Pagantis\OrdersApiClient\Client $orderClient
     */
    protected $orderClient;

    /**
     * @var mixed $config
     */
    protected $config;

    /**
     * @var Object $jsonResponse
     */
    protected $jsonResponse;

    /**
     * @throws Exception
     */
    public function postProcess()
    {
        try {
            $this->checkConcurrency();
            $this->getMerchantOrder();
            $this->getPagantisOrderId();
            $this->getPagantisOrder();
            $this->checkOrderStatus();
            $this->checkMerchantOrderStatus();
            $this->validateAmount();
            $this->processMerchantOrder();
        } catch (\Exception $exception) {
            $this->jsonResponse = new JsonExceptionResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            $this->jsonResponse->setException($exception);
            return $this->cancelProcess($this->jsonResponse);
        }

        try {
            $this->jsonResponse = new JsonSuccessResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            $this->confirmPagantisOrder();
        } catch (\Exception $exception) {
            $this->rollbackMerchantOrder();
            $this->jsonResponse = new JsonExceptionResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            $this->jsonResponse->setException($exception);
            return $this->cancelProcess($this->jsonResponse);
        }

        try {
            $this->unblockConcurrency();
        } catch (\Exception $exception) {
            // Do nothing
        }

        return $this->finishProcess(false);
    }

    /**
     * Check the concurrency of the purchase
     *
     * @throws Exception
     */
    public function checkConcurrency()
    {
        $this->prepareVariables();
        $this->unblockConcurrency();
        $this->blockConcurrency($this->merchantOrderId);
    }

    /**
     * Find and init variables needed to process payment
     *
     * @throws Exception
     */
    public function prepareVariables()
    {
        $this->processError = false;
        $callbackOkUrl = $this->context->link->getPageLink(
            'order-confirmation',
            null,
            null
        );
        $callbackKoUrl = $this->context->link->getPageLink(
            'order',
            null,
            null,
            array('step'=>3)
        );
        try {
            $this->config = array(
                'urlOK' => (Pagantis::getExtraConfig('PAGANTIS_URL_OK') !== '') ?
                    Pagantis::getExtraConfig('PAGANTIS_URL_OK') : $callbackOkUrl,
                'urlKO' => (Pagantis::getExtraConfig('PAGANTIS_URL_KO') !== '') ?
                    Pagantis::getExtraConfig('PAGANTIS_URL_KO') : $callbackKoUrl,
                'publicKey' => Configuration::get('pagantis_public_key'),
                'privateKey' => Configuration::get('pagantis_private_key'),
                'secureKey' => Tools::getValue('key'),
            );
        } catch (\Exception $exception) {
            throw new ConfigurationNotFoundException();
        }

        $this->merchantOrderId = Tools::getValue('id_cart');
        if ($this->merchantOrderId == '') {
            throw new QuoteNotFoundException();
        }


        if (!($this->config['secureKey'] && $this->merchantOrderId && Module::isEnabled(self::PAGANTIS_CODE))) {
            // This exception is only for Prestashop
            throw new UnknownException('Module may not be enabled');
        }
    }

    /**
     * Retrieve the merchant order by id
     *
     * @throws Exception
     */
    public function getMerchantOrder()
    {
        try {
            $this->merchantOrder = new Cart($this->merchantOrderId);
            if (!Validate::isLoadedObject($this->merchantOrder)) {
                // This exception is only for Prestashop
                throw new UnknownException('Unable to load cart');
            }
        } catch (\Exception $exception) {
            throw new MerchantOrderNotFoundException();
        }
    }

    /**
     * Find PAGANTIS Order Id in AbstractController::PAGANTIS_ORDERS_TABLE
     *
     * @throws Exception
     */
    private function getPagantisOrderId()
    {
        try {
            $this->pagantisOrderId= Db::getInstance()->getValue(
                'select order_id from '._DB_PREFIX_.'pagantis_order where id = '.$this->merchantOrderId
            );

            if (is_null($this->pagantisOrderId)) {
                throw new NoIdentificationException();
            }
        } catch (\Exception $exception) {
            throw new NoIdentificationException();
        }
    }

    /**
     * Find PAGANTIS Order in Orders Server using Pagantis\OrdersApiClient
     *
     * @throws Exception
     */
    private function getPagantisOrder()
    {
        $this->orderClient = new PagantisClient($this->config['publicKey'], $this->config['privateKey']);
        $this->pagantisOrder = $this->orderClient->getOrder($this->pagantisOrderId);
        if (!($this->pagantisOrder instanceof PagantisModelOrder)) {
            throw new OrderNotFoundException();
        }
    }

    /**
     * Compare statuses of merchant order and PAGANTIS order, witch have to be the same.
     *
     * @throws Exception
     */
    public function checkOrderStatus()
    {
        if ($this->pagantisOrder->getStatus() === PagantisModelOrder::STATUS_CONFIRMED) {
            $this->jsonResponse = new JsonSuccessResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            return $this->finishProcess(false);
        }

        if ($this->pagantisOrder->getStatus() !== PagantisModelOrder::STATUS_AUTHORIZED) {
            $status = '-';
            if ($this->pagantisOrder instanceof \Pagantis\OrdersApiClient\Model\Order) {
                $status = $this->pagantisOrder->getStatus();
            }
            throw new WrongStatusException($status);
        }
    }

    /**
     * Check that the merchant order was not previously processes and is ready to be paid
     *
     * @throws Exception
     */
    public function checkMerchantOrderStatus()
    {
        if ($this->merchantOrder->orderExists() !== false) {
            throw new WrongStatusException('already_processed');
        }
    }

    /**
     * Check that the merchant order and the order in PAGANTIS have the same amount to prevent hacking
     *
     * @throws Exception
     */
    public function validateAmount()
    {
        $totalAmount = $this->pagantisOrder->getShoppingCart()->getTotalAmount();
        $merchantAmount = (int) (100 * $this->merchantOrder->getOrderTotal(true));
        if ($totalAmount != $merchantAmount) {
            $this->processError = true;
            throw new AmountMismatchException($totalAmount, $merchantAmount);
        }
    }

    /**
     * Process the merchant order and notify client
     *
     * @throws Exception
     */
    public function processMerchantOrder()
    {
        try {
            $this->module->validateOrder(
                $this->merchantOrderId,
                Configuration::get('PS_OS_PAYMENT'),
                $this->merchantOrder->getOrderTotal(true),
                $this->module->displayName,
                ' pagantisOrderId: ' . $this->pagantisOrder->getId().
                ' pagantisOrderStatus: '. $this->pagantisOrder->getStatus(),
                array('transaction_id' => $this->pagantisOrderId),
                null,
                false,
                $this->config['secureKey']
            );
        } catch (\Exception $exception) {
            throw new UnknownException($exception->getMessage());
        }
    }

    /**
     * Confirm the order in PAGANTIS
     *
     * @throws Exception
     */
    private function confirmPagantisOrder()
    {
        try {
            $this->orderClient->confirmOrder($this->pagantisOrderId);
            try {
                $mode = ($_SERVER['REQUEST_METHOD'] == 'POST') ? 'NOTIFICATION' : 'REDIRECTION';
                $message = 'Order CONFIRMED. The order was confirmed by a ' . $mode .
                    '. Pagantis OrderId=' . $this->pagantisOrderId .
                    '. Prestashop OrderId=' . $this->merchantOrderId;
                $this->saveLog(array(
                    'message' => $message
                ));
            } catch (\Exception $e) {
                // Do nothing
            }
        } catch (\Exception $exception) {
            throw new UnknownException($exception->getMessage());
        }
    }

    /**
     * Leave the merchant order as it was previously
     *
     * @throws Exception
     */
    public function rollbackMerchantOrder()
    {
        // Do nothing because the order is created only when the purchase was successfully
    }

    /**
     * Lock the concurrency to prevent duplicated inputs
     *
     * @param $orderId
     * @throws Exception
     */
    protected function blockConcurrency($orderId)
    {
        try {
            Db::getInstance()->insert('pagantis_cart_process', array('id' => $orderId, 'timestamp' => (time())));
        } catch (\Exception $exception) {
            throw new ConcurrencyException();
        }
    }

    /**
     * Unlock the concurrency
     *
     * @throws Exception
     */
    protected function unblockConcurrency()
    {
        try {
            Db::getInstance()->delete('pagantis_cart_process', 'timestamp < ' . (time() - 6));
        } catch (\Exception $exception) {
            throw new ConcurrencyException();
        }
    }

    /**
     * Do all the necessary actions to cancel the confirmation process in case of error
     * 1. Unblock concurrency
     * 2. Save log
     *
     * @param String|null $response Response as json
     *
     */
    public function cancelProcess($response = null)
    {
        if ($this->merchantOrder && $this->processError === true) {
            sleep(5);
            $id = (!is_null($this->pagantisOrder))?$this->pagantisOrder->getId():null;
            $status = (!is_null($this->pagantisOrder))?$this->pagantisOrder->getStatus():null;
            $this->module->validateOrder(
                $this->merchantOrderId,
                Configuration::get('PS_OS_ERROR'),
                $this->merchantOrder->getOrderTotal(true),
                $this->module->displayName,
                ' pagantisOrderId: ' . $id.
                ' pagantisOrderStatus: '. $status,
                null,
                null,
                false,
                $this->config['secureKey']
            );
        }

        $debug = debug_backtrace();
        $method = $debug[1]['function'];
        $line = $debug[1]['line'];
        $this->saveLog(array(
            'message' => $response,
            'method' => $method,
            'file' => __FILE__,
            'line' => $line,
            'code' => 200
        ));
        return $this->finishProcess(true);
    }

    /**
     * Redirect the request to the e-commerce or show the output in json
     *
     * @param bool $error
     */
    public function finishProcess($error = true)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->jsonResponse->printResponse();
        }

        $parameters = array(
            'id_cart' => $this->merchantOrderId,
            'key' => $this->config['secureKey'],
            'id_module' => $this->module->id,
            'id_order' => ($this->pagantisOrder)?$this->pagantisOrder->getId(): null,
        );
        $url = ($error)? $this->config['urlKO'] : $this->config['urlOK'];
        return $this->redirect($url, $parameters);
    }
}
