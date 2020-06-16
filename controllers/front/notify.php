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
     * Seconds to expire a locked request
     */
    const CONCURRENCY_TIMEOUT = 10;

    /**
     * @var string $productName
     */
    protected $productName;

    /**
     * @var int $merchantOrderId
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
     * @var string $amountMismatchError
     */
    protected $amountMismatchError = '';

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
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // prevent colision between POST and GET requests
                sleep(15);
            }
            if (Tools::getValue('origin') == 'notification' && $_SERVER['REQUEST_METHOD'] == 'GET') {
                return $this->cancelProcess();
            }
            $this->prepareVariables();
            $this->checkConcurrency();
            $this->getMerchantOrder();
            $this->getPagantisOrderId();
            $this->getPagantisOrder();
            if ($this->checkOrderStatus()) {
                return $this->finishProcess(false);
            }
            $this->validateAmount();
            if ($this->checkMerchantOrderStatus()) {
                $this->processMerchantOrder();
            }
        } catch (\Exception $exception) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->jsonResponse = new JsonExceptionResponse();
                $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
                $this->jsonResponse->setPagantisOrderId($this->pagantisOrderId);
                $this->jsonResponse->setException($exception);
            }
            return $this->cancelProcess($exception);
        }

        try {
            $this->jsonResponse = new JsonSuccessResponse();
            $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $this->jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            $this->confirmPagantisOrder();
        } catch (\Exception $exception) {
            $this->rollbackMerchantOrder();
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->jsonResponse = new JsonExceptionResponse();
                $this->jsonResponse->setMerchantOrderId($this->merchantOrderId);
                $this->jsonResponse->setPagantisOrderId($this->pagantisOrderId);
                $this->jsonResponse->setException($exception);
            }
            return $this->cancelProcess($exception);
        }

        try {
            $this->unblockConcurrency($this->merchantOrderId);
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
            $productName = Tools::getValue('product');
            $this->productName = (!empty($productName)) ? Tools::getValue('product') : "Pagantis";
            if ($this->productName === "Pagantis PMT") {
                $pagantisPublicKey = Configuration::get('pagantis_public_key_later');
                $pagantisPrivateKey = Configuration::get('pagantis_private_key_later');
            } else {
                $pagantisPublicKey = Configuration::get('pagantis_public_key');
                $pagantisPrivateKey = Configuration::get('pagantis_private_key');
            }
            $this->config = array(
                'urlOK' => (Pagantis::getExtraConfig('PAGANTIS_URL_OK') !== '') ?
                    Pagantis::getExtraConfig('PAGANTIS_URL_OK') : $callbackOkUrl,
                'urlKO' => (Pagantis::getExtraConfig('PAGANTIS_URL_KO') !== '') ?
                    Pagantis::getExtraConfig('PAGANTIS_URL_KO') : $callbackKoUrl,
                'publicKey' => $pagantisPublicKey,
                'privateKey' => $pagantisPrivateKey,
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
            if ($this->merchantOrder->secure_key != $this->config['secureKey']) {
                throw new UnknownException('Secure Key is not valid');
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
                'select order_id from '._DB_PREFIX_.'pagantis_order where id = '.(int)$this->merchantOrderId
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
            return true;
        }

        if ($this->pagantisOrder->getStatus() !== PagantisModelOrder::STATUS_AUTHORIZED) {
            $status = '-';
            if ($this->pagantisOrder instanceof \Pagantis\OrdersApiClient\Model\Order) {
                $status = $this->pagantisOrder->getStatus();
            }
            throw new WrongStatusException($status);
        }
        return false;
    }

    /**
     * Check that the merchant order and the order in PAGANTIS have the same amount to prevent hacking
     *
     * @throws Exception
     */
    public function validateAmount()
    {
        $totalAmount = (string) $this->pagantisOrder->getShoppingCart()->getTotalAmount();
        $merchantAmount = (string) (100 * $this->merchantOrder->getOrderTotal(true));
        $merchantAmount = explode('.', explode(',', $merchantAmount)[0])[0];
        if ($totalAmount != $merchantAmount) {
            try {
                $psTotalAmount = substr_replace($merchantAmount, '.', (Tools::strlen($merchantAmount) -2), 0);

                $pgTotalAmountInCents = (string) $this->pagantisOrder->getShoppingCart()->getTotalAmount();
                $pgTotalAmount = substr_replace(
                    $pgTotalAmountInCents,
                    '.',
                    (Tools::strlen($pgTotalAmountInCents) -2),
                    0
                );

                $this->amountMismatchError = '. Amount mismatch in PrestaShop Order #'. $this->merchantOrderId .
                    ' compared with Pagantis Order: ' . $this->pagantisOrderId .
                    '. The order in PrestaShop has an amount of ' . $psTotalAmount . ' and in Pagantis ' .
                    $pgTotalAmount . ' PLEASE REVIEW THE ORDER';
                $this->saveLog(array(
                    'message' => $this->amountMismatchError
                ));
            } catch (\Exception $exception) {
                // Do nothing
            }
        }
    }

    /**
     * Check that the merchant order was not previously processes and is ready to be paid
     *
     * @throws Exception
     */
    public function checkMerchantOrderStatus()
    {
        try {
            if ($this->merchantOrder->orderExists() !== false) {
                throw new WrongStatusException('PS->orderExists() cart_id = '
                    . $this->merchantOrderId . ' pagantis_id = '
                    . $this->pagantisOrderId . '): already_processed');
            }

            // Double check
            $tableName = _DB_PREFIX_ . 'pagantis_order';
            $fieldName = 'ps_order_id';
            $sql = ('select ' . $fieldName . ' from `' . $tableName . '` where `id` = ' . (int)$this->merchantOrderId
                . ' and `order_id` = \'' . $this->pagantisOrderId . '\''
                . ' and `' . $fieldName . '` is not null');
            $results = Db::getInstance()->ExecuteS($sql);
            if (is_array($results) && count($results) === 1) {
                throw new WrongStatusException('PS->record found in ' . $tableName
                    . ' (cart_id = ' . $this->merchantOrderId . ' pagantis_id = '
                    . $this->pagantisOrderId . '): already_processed');
            }
        } catch (\Exception $exception) {
            throw new UnknownException($exception->getMessage());
        }
        return true;
    }

    /**
     * Process the merchant order and notify client
     *
     * @throws Exception
     */
    public function processMerchantOrder()
    {
        try {
            $metadataOrder = $this->pagantisOrder->getMetadata();
            $metadataInfo = '';
            foreach ($metadataOrder as $metadataKey => $metadataValue) {
                if ($metadataKey == 'promotedProduct') {
                    $metadataInfo .= $metadataValue;
                }
            }

            $this->module->validateOrder(
                $this->merchantOrderId,
                Configuration::get('PS_OS_PAYMENT'),
                $this->merchantOrder->getOrderTotal(true),
                $this->productName,
                'pagantisOrderId: ' . $this->pagantisOrder->getId() . ' ' .
                'pagantisOrderStatus: '. $this->pagantisOrder->getStatus() .
                $this->amountMismatchError .
                $metadataInfo,
                array('transaction_id' => $this->pagantisOrderId),
                null,
                false,
                $this->config['secureKey']
            );
        } catch (\Exception $exception) {
            throw new UnknownException($exception->getMessage());
        }
        try {
            Db::getInstance()->update(
                'pagantis_order',
                array('ps_order_id' => $this->module->currentOrder),
                'id = '. (int)$this->merchantOrderId . ' and order_id = \'' . $this->pagantisOrderId . '\''
            );
        } catch (\Exception $exception) {
            // Do nothing
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
                    '. Prestashop OrderId=' . $this->module->currentOrder;
                $this->saveLog(array('message' => $message));
            } catch (\Exception $exception) {
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
        try {
            $message = 'Roolback method: ' .
                '. Pagantis OrderId=' . $this->pagantisOrderId .
                '. Prestashop CartId=' . $this->merchantOrderId;
            if ($this->module->currentOrder) {
                $objOrder = new Order($this->module->currentOrder);
                $history = new OrderHistory();
                $history->id_order = (int)$objOrder->id;
                $history->changeIdOrderState(8, (int)($objOrder->id));
                $message .= ' Prestashop OrderId=' . $this->merchantOrderId;
            }
            $this->saveLog(array('message' => $message));
        } catch (\Exception $exception) {
            $this->saveLog(array('message' => $exception->getMessage()));
        }
    }

    /**
     * Lock the concurrency to prevent duplicated inputs
     *
     * @param $orderId
     * @return bool|void
     * @throws ConcurrencyException
     */
    protected function blockConcurrency($orderId)
    {
        try {
            $table = 'pagantis_cart_process';
            if (Db::getInstance()->insert($table, array('id' => (int)$orderId, 'timestamp' => (time()))) === false) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    throw new ConcurrencyException();
                }

                $query = sprintf(
                    "SELECT TIMESTAMPDIFF(SECOND,NOW()-INTERVAL %s SECOND, FROM_UNIXTIME(timestamp)) as rest 
                            FROM %s WHERE %s",
                    self::CONCURRENCY_TIMEOUT,
                    _DB_PREFIX_.$table,
                    'id='.(int)$orderId
                );
                $resultSeconds = Db::getInstance()->getValue($query);
                $restSeconds = isset($resultSeconds) ? ($resultSeconds) : 0;
                $secondsToExpire = ($restSeconds>self::CONCURRENCY_TIMEOUT) ? self::CONCURRENCY_TIMEOUT : $restSeconds;

                $logMessage = sprintf(
                    "Redirect concurrency, User have to wait %s seconds, default seconds %s. CartId=" . $orderId,
                    $secondsToExpire,
                    self::CONCURRENCY_TIMEOUT,
                    $restSeconds
                );

                $this->saveLog(array(
                    'message' => $logMessage
                ));
                sleep($secondsToExpire+1);
                // After waiting...user continue the confirmation, hoping that previous call have finished.
                return true;
            }
        } catch (\Exception $exception) {
            throw new ConcurrencyException();
        }
    }

    /**
     * @param null $orderId
     *
     * @throws ConcurrencyException
     */
    private function unblockConcurrency($orderId = null)
    {
        try {
            if (is_null($orderId)) {
                Db::getInstance()->delete(
                    'pagantis_cart_process',
                    'timestamp < ' . (time() - self::CONCURRENCY_TIMEOUT)
                );
                return;
            }
            Db::getInstance()->delete('pagantis_cart_process', 'id = ' . (int)$orderId);
        } catch (\Exception $exception) {
            throw new ConcurrencyException();
        }
    }

    /**
     * Do all the necessary actions to cancel the confirmation process in case of error
     * 1. Unblock concurrency
     * 2. Save log
     *
     * @param null $exception
     * @return mixed
     */
    public function cancelProcess($exception = null)
    {
        $debug = debug_backtrace();
        $method = $debug[1]['function'];
        $line = $debug[1]['line'];
        $data = array(
            'merchantOrderId' => $this->merchantOrderId,
            'pagantisOrderId' => $this->pagantisOrderId,
            'message' => ($exception)? $exception->getMessage() : 'Unable to get Exception message',
            'statusCode' => ($exception)? $exception->getCode() : 'Unable to get Exception statusCode',
            'method' => $method,
            'file' => __FILE__,
            'line' => $line,
        );
        $this->saveLog($data);
        return $this->finishProcess(true);
    }

    /**
     * Redirect the request to the e-commerce or show the output in json
     *
     * @param bool $error
     * @return mixed
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
