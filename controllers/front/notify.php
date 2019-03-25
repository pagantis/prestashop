<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2019 Paga+Tarde
 * @license   proprietary
 */

require_once('AbstractController.php');

use PagaMasTarde\OrdersApiClient\Client as PmtClient;
use PagaMasTarde\OrdersApiClient\Model\Order as PmtModelOrder;
use PagaMasTarde\ModuleUtils\Exception\AmountMismatchException;
use PagaMasTarde\ModuleUtils\Exception\ConcurrencyException;
use PagaMasTarde\ModuleUtils\Exception\MerchantOrderNotFoundException;
use PagaMasTarde\ModuleUtils\Exception\NoIdentificationException;
use PagaMasTarde\ModuleUtils\Exception\OrderNotFoundException;
use PagaMasTarde\ModuleUtils\Exception\QuoteNotFoundException;
use PagaMasTarde\ModuleUtils\Exception\ConfigurationNotFoundException;
use PagaMasTarde\ModuleUtils\Exception\UnknownException;
use PagaMasTarde\ModuleUtils\Exception\WrongStatusException;
use PagaMasTarde\ModuleUtils\Model\Response\JsonSuccessResponse;
use PagaMasTarde\ModuleUtils\Model\Response\JsonExceptionResponse;

/**
 * Class PaylaterNotifyModuleFrontController
 */
class PaylaterNotifyModuleFrontController extends AbstractController
{
    /**
     * @var bool $error
     */
    protected $error;

    /**
     * @var string $merchantOrderId
     */
    protected $merchantOrderId;

    /**
     * @var \Cart $merchantOrder
     */
    protected $merchantOrder;

    /**
     * @var string $pmtOrderId
     */
    protected $pmtOrderId;

    /**
     * @var \PagaMasTarde\OrdersApiClient\Model\Order $pmtOrder
     */
    protected $pmtOrder;

    /**
     * @var PagaMasTarde\OrdersApiClient\Client $orderClient
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
            $this->getPmtOrderId();
            $this->getPmtOrder();
            $this->checkOrderStatus();
            $this->checkMerchantOrderStatus();
            $this->validateAmount();
            $this->processMerchantOrder();
        } catch (\Exception $exception) {
            $this->jsonResponse = new JsonExceptionResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPmtOrderId($this->pmtOrderId);
            $this->jsonResponse->setException($exception);
            $response = $this->jsonResponse->toJson();
            return $this->cancelProcess($response);
        }

        try {
            $this->jsonResponse = new JsonSuccessResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPmtOrderId($this->pmtOrderId);
            $this->confirmPmtOrder();
        } catch (\Exception $exception) {
            $this->rollbackMerchantOrder();
            $this->jsonResponse = new JsonExceptionResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPmtOrderId($this->pmtOrderId);
            $this->jsonResponse->setException($exception);
            $response = $this->jsonResponse->toJson();
            return $this->cancelProcess($response);
        }

        try {
            $this->unblockConcurrency();
        } catch (\Exception $exception) {
            // Do nothing
        }

        return $this->finishProcess(false);
    }

    /**
     * Find and init variables needed to process payment
     *
     * @throws Exception
     */
    public function prepareVariables()
    {
        $this->error = false;
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
                'urlOK' => (getenv('PMT_URL_OK') !== '') ? getenv('PMT_URL_OK') : $callbackOkUrl,
                'urlKO' => (getenv('PMT_URL_KO') !== '') ? getenv('PMT_URL_KO') : $callbackKoUrl,
                'publicKey' => Configuration::get('pmt_public_key'),
                'privateKey' => Configuration::get('pmt_private_key'),
                'secureKey' => Tools::getValue('key'),
            );
        } catch (\Exception $exception) {
            throw new ConfigurationNotFoundException();
        }

        $this->merchantOrderId = Tools::getValue('id_cart');
        if ($this->merchantOrderId == '') {
            throw new QuoteNotFoundException();
        }


        if (!($this->config['secureKey'] && $this->merchantOrderId && Module::isEnabled(self::PMT_CODE))) {
            // This exception is only for Prestashop
            throw new UnknownException('Module may not be enabled');
        }
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
     * Find PMT Order Id in AbstractController::PMT_ORDERS_TABLE
     *
     * @throws Exception
     */
    private function getPmtOrderId()
    {
        try {
            $this->pmtOrderId= Db::getInstance()->getValue(
                'select order_id from '._DB_PREFIX_.'pmt_order where id = '.$this->merchantOrderId
            );

            if (is_null($this->pmtOrderId)) {
                throw new NoIdentificationException();
            }
        } catch (\Exception $exception) {
            throw new NoIdentificationException();
        }
    }

    /**
     * Find PMT Order in Orders Server using PagaMasTarde\OrdersApiClient
     *
     * @throws Exception
     */
    private function getPmtOrder()
    {
        $this->orderClient = new PmtClient($this->config['publicKey'], $this->config['privateKey']);
        $this->pmtOrder = $this->orderClient->getOrder($this->pmtOrderId);
        if (!($this->pmtOrder instanceof PmtModelOrder)) {
            throw new OrderNotFoundException();
        }
    }

    /**
     * Compare statuses of merchant order and PMT order, witch have to be the same.
     *
     * @throws Exception
     */
    public function checkOrderStatus()
    {
        if ($this->pmtOrder->getStatus() !== PmtModelOrder::STATUS_AUTHORIZED) {
            $status = '-';
            if ($this->pmtOrder instanceof \PagaMasTarde\OrdersApiClient\Model\Order) {
                $status = $this->pmtOrder->getStatus();
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
     * Check that the merchant order and the order in PMT have the same amount to prevent hacking
     *
     * @throws Exception
     */
    public function validateAmount()
    {
        $totalAmount = $this->pmtOrder->getShoppingCart()->getTotalAmount();
        $merchantAmount = (int)((string) (100 * $this->merchantOrder->getOrderTotal(true)));
        if ($totalAmount != $merchantAmount) {
            $this->error = true;
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
                'pmtOrderId: ' . $this->pmtOrder->getId(),
                array('transaction_id' => $this->pmtOrderId),
                null,
                false,
                $this->config['secureKey']
            );
        } catch (\Exception $exception) {
            throw new UnknownException($exception->getMessage());
        }
    }

    /**
     * Confirm the order in PMT
     *
     * @throws Exception
     */
    private function confirmPmtOrder()
    {
        try {
            $this->orderClient->confirmOrder($this->pmtOrderId);
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
            Db::getInstance()->insert('pmt_cart_process', array('id' => $orderId, 'timestamp' => (time())));
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
            Db::getInstance()->delete('pmt_cart_process', 'timestamp < ' . (time() - 6));
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
       if ($this->merchantOrder && $this->error === true) {
            sleep(5);
            $id = (!is_null($this->pmtOrder))?$this->pmtOrder->getId():null;
            $status = (!is_null($this->pmtOrder))?$this->pmtOrder->getStatus():null;
            $this->module->validateOrder(
                $this->merchantOrderId,
                Configuration::get('PS_OS_ERROR'),
                $this->merchantOrder->getOrderTotal(true),
                $this->module->displayName,
                ' pmtOrderId: ' . $id .
                ' pmtStatusId:' . $status,
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
            'id_order' => ($this->pmtOrder)?$this->pmtOrder->getId(): null,
        );
        $url = ($error)? $this->config['urlKO'] : $this->config['urlOK'];
        return $this->redirect($url, $parameters);
    }
}
