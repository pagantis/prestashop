<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
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
use Pagantis\ModuleUtils\Exception\UnknownException;
use Pagantis\ModuleUtils\Exception\WrongStatusException;
use Pagantis\ModuleUtils\Model\Response\JsonSuccessResponse;
use Pagantis\ModuleUtils\Model\Response\JsonExceptionResponse;

/**
 * Class ClearpayNotifyModuleFrontController
 */
class ClearpayNotifyModuleFrontController extends AbstractController
{
    /** Cart tablename */
    const CART_TABLE = 'clearpay_cart_process';

    /** Clearpay orders tablename */
    const ORDERS_TABLE = 'clearpay_order';

    /**
     * Seconds to expire a locked request
     */
    const CONCURRENCY_TIMEOUT = 10;

    /**
     * @var string $token
     */
    protected $token;

    /**
     * @var string $productName
     */
    protected $productName;

    /**
     * @var int $requestId
     */
    protected $requestId = null;

    /**
     * @var int $merchantOrderId
     */
    protected $merchantOrderId = null;

    /**
     * @var \Order $merchantOrder
     */
    protected $merchantOrder;

    /**
     * @var int $merchantCartId
     */
    protected $merchantCartId;

    /**
     * @var \Cart $merchantCart
     */
    protected $merchantCart;

    /**
     * @var string $clearpayOrderId
     */
    protected $clearpayOrderId;

    /**
     * @var string $amountMismatchError
     */
    protected $amountMismatchError = '';

    /**
     * @var \Pagantis\OrdersApiClient\Model\Order $clearpayOrder
     */
    protected $clearpayOrder;

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

    /** @var mixed $origin */
    protected $origin;

    /**
     * @throws Exception
     */
    public function postProcess()
    {
        $thrownException = false;
        $this->origin = ($this->isPost() || Tools::getValue('origin') === 'notification') ? 'Notification' : 'Order';
        $this->requestId = rand(1, 999999999);

        // Validations
        try {
            //Avoiding notifications via GET
            if ($this->isGet() && $this->isNotification()) {
                echo 'OK';
                die;
            }

            $redirectMessage = sprintf(
                "Request [origin=%s][cartId=%s]",
                $this->getOrigin(),
                Tools::getValue('id_cart')
            );
            $this->saveLog(array('requestId' => $this->requestId, 'message' => $redirectMessage));

            $this->prepareVariables();
            $this->checkConcurrency();
            $this->getMerchantOrder();
            $this->getClearpayOrderId();
            $this->getClearpayOrder();
            if ($this->checkOrderStatus()) {
                $thrownException = true;
                return $this->finishProcess(false);
            }
            $this->validateAmount();
            $this->checkMerchantOrderStatus();
        } catch (\Exception $exception) {
            $thrownException = true;
            $this->getMerchantOrderId();
            $theId = ($this->merchantOrderId)? $this->merchantOrderId : $this->merchantCartId;
            if ($this->isPost()) {
                $this->jsonResponse = new JsonExceptionResponse();
                $this->jsonResponse->setMerchantOrderId($theId);
                $this->jsonResponse->setClearpayOrderId($this->clearpayOrderId);
                $this->jsonResponse->setException($exception);
            }
            return $this->cancelProcess($exception);
        }

        // Proccess Clearpay Order
        try {
            if (!$thrownException) {
                $this->jsonResponse = new JsonSuccessResponse();
                $this->getMerchantOrderId();
                $theId = ($this->merchantOrderId)? $this->merchantOrderId : $this->merchantCartId;
                $this->jsonResponse->setMerchantOrderId($theId);
                $this->jsonResponse->setClearpayOrderId($this->clearpayOrderId);
                $this->confirmClearpayOrder();
            }
        } catch (\Exception $exception) {
            $this->rollbackMerchantOrder();
            if ($this->isNotification()) {
                $this->getMerchantOrderId();
                $theId = ($this->merchantOrderId)? $this->merchantOrderId : $this->merchantCartId;
                $this->jsonResponse = new JsonExceptionResponse();
                $this->jsonResponse->setMerchantOrderId($theId);
                $this->jsonResponse->setClearpayOrderId($this->clearpayOrderId);
                $this->jsonResponse->setException($exception);
            }
            return $this->cancelProcess($exception);
        }

        // Process Merchant Order
        try {
            if (!$thrownException) {
                $this->processMerchantOrder();
            }
        } catch (\Exception $exception) {
            $thrownException = true;
            $this->getMerchantOrderId();
            $theId = ($this->merchantOrderId)? $this->merchantOrderId : $this->merchantCartId;
            if ($this->isPost()) {
                $this->jsonResponse = new JsonExceptionResponse();
                $this->jsonResponse->setMerchantOrderId($theId);
                $this->jsonResponse->setClearpayOrderId($this->clearpayOrderId);
                $this->jsonResponse->setException($exception);
            }
            return $this->cancelProcess($exception);
        }

        try {
            $this->unblockConcurrency($this->merchantCartId);
        } catch (\Exception $exception) {
            $exceptionMessage = sprintf(
                "unblocking exception[origin=%s][cartId=%s][merchantOrderId=%s][clearpayOrderId=%s][%s]",
                $this->getOrigin(),
                $this->merchantCartId,
                $this->merchantOrderId,
                $this->clearpayOrderId,
                $exception->getMessage()
            );
            $this->saveLog(array('requestId' => $this->requestId, 'message' => $exceptionMessage));
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
        $this->blockConcurrency($this->merchantCartId);
    }

    /**
     * Find and init variables needed to process payment
     *
     * @throws Exception
     */
    public function prepareVariables()
    {
        $this->getMerchantOrderId();
        if (!empty($this->merchantOrderId)) {
            $exceptionMessage = sprintf(
                "The order %s already exists in %s table",
                $this->merchantOrderId,
                self::ORDERS_TABLE
            );
            throw new UnknownException($exceptionMessage);
        }
        $callbackOkUrl = $this->context->link->getPageLink('order-confirmation', null, null);
        $callbackKoUrl = $this->context->link->getPageLink('order', null, null, array('step'=>3));

        $this->config = array(
            'urlOK' => (Clearpay::getExtraConfig('URL_OK') !== '') ?
                Clearpay::getExtraConfig('URL_OK') : $callbackOkUrl,
            'urlKO' => (Clearpay::getExtraConfig('URL_KO') !== '') ?
                Clearpay::getExtraConfig('URL_KO') : $callbackKoUrl,
            'secureKey' => Tools::getValue('key'),
        );
        $productCode = Tools::getValue('product');
        $this->token = Tools::getValue('token');
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        if (!in_array(Tools::strtoupper($productCode), $products)) {
            throw new UnknownException(
                'No valid Clearpay product provided in the url: ' . Tools::getValue('product')
            );
        }
        $this->productName = "Clearpay " . Tools::strtolower($productCode);

        $this->config['publicKey'] = trim(Configuration::get(Tools::strtolower($productCode) . '_public_key'));
        $this->config['privateKey'] = trim(Configuration::get(Tools::strtolower($productCode) . '_private_key'));

        $this->merchantCartId = Tools::getValue('id_cart');

        if ($this->merchantCartId == '') {
            throw new QuoteNotFoundException();
        }

        if (!($this->config['secureKey'] && Module::isEnabled(self::CODE))) {
            // This exception is only for Prestashop
            throw new UnknownException('Module may not be enabled');
        }
    }

    /**
     * Find prestashop Order Id
     */
    public function getMerchantOrderId()
    {
        try {
            $sql = 'select ps_order_id from ' . _DB_PREFIX_ .self::ORDERS_TABLE .' where id = '
                .(int)$this->merchantCartId . ' and token = \'' . $this->token . '\'';
            $this->merchantOrderId = Db::getInstance()->getValue($sql);
        } catch (\Exception $exception) {
            $exceptionMessage = sprintf(
                "getMerchantOrderId exception[origin=%s][cartId=%s][merchantOrderId=%s][clearpayOrderId=%s][%s]",
                $this->getOrigin(),
                $this->merchantCartId,
                $this->merchantOrderId,
                $this->clearpayOrderId,
                $exception->getMessage()
            );
            $this->saveLog(array('requestId' => $this->requestId, 'message' => $exceptionMessage));
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
            $this->merchantCart = new Cart($this->merchantCartId);
            if (!Validate::isLoadedObject($this->merchantCart)) {
                // This exception is only for Prestashop
                throw new UnknownException('Unable to load cart');
            }
            if ($this->merchantCart->secure_key != $this->config['secureKey']) {
                throw new UnknownException('Secure Key is not valid');
            }
        } catch (\Exception $exception) {
            throw new MerchantOrderNotFoundException();
        }
    }

    /**
     * Find PAGANTIS Order Id
     *
     * @throws Exception
     */
    private function getClearpayOrderId()
    {
        try {
            $sql = 'select order_id from ' . _DB_PREFIX_.self::ORDERS_TABLE .' where id = '
                .(int)$this->merchantCartId . ' and token = \'' . $this->token . '\'';
            $this->clearpayOrderId= Db::getInstance()->getValue($sql);

            if (is_null($this->clearpayOrderId)) {
                throw new NoIdentificationException();
            }
        } catch (\Exception $exception) {
            throw new NoIdentificationException();
        }
    }

    /**
     * Find PAGANTIS Order in Orders Server using Clearpay\OrdersApiClient
     *
     * @throws Exception
     */
    private function getClearpayOrder()
    {
        $this->orderClient = new PagantisClient($this->config['publicKey'], $this->config['privateKey']);
        $this->clearpayOrder = $this->orderClient->getOrder($this->clearpayOrderId);
        if (!($this->clearpayOrder instanceof PagantisModelOrder)) {
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
        if ($this->clearpayOrder->getStatus() === PagantisModelOrder::STATUS_CONFIRMED) {
            $this->getMerchantOrderId();
            $theId = ($this->merchantOrderId)? $this->merchantOrderId : $this->merchantCartId;
            $this->jsonResponse = new JsonSuccessResponse();
            $this->jsonResponse->setMerchantOrderId($theId);
            $this->jsonResponse->setClearpayOrderId($this->clearpayOrderId);
            return true;
        }

        if ($this->clearpayOrder->getStatus() !== PagantisModelOrder::STATUS_AUTHORIZED) {
            $status = '-';
            if ($this->clearpayOrder instanceof \Pagantis\OrdersApiClient\Model\Order) {
                $status = $this->clearpayOrder->getStatus();
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
        $totalAmount = (string) $this->clearpayOrder->getShoppingCart()->getTotalAmount();
        $merchantAmount = (string) (100 * $this->merchantCart->getOrderTotal(true));
        $merchantAmount = explode('.', explode(',', $merchantAmount)[0])[0];
        if ($totalAmount != $merchantAmount) {
            try {
                $psTotalAmount = substr_replace(
                    $merchantAmount,
                    '.',
                    (Tools::strlen($merchantAmount) -2),
                    0
                );

                $pgTotalAmountInCents = (string) $this->clearpayOrder->getShoppingCart()->getTotalAmount();
                $pgTotalAmount = substr_replace(
                    $pgTotalAmountInCents,
                    '.',
                    (Tools::strlen($pgTotalAmountInCents) -2),
                    0
                );

                $this->amountMismatchError = '. Amount mismatch in PrestaShop Cart #'. $this->merchantCartId .
                    ' compared with Clearpay Order: ' . $this->clearpayOrderId .
                    '. The Cart in PrestaShop has an amount of ' . $psTotalAmount . ' and in Clearpay ' .
                    $pgTotalAmount . ' PLEASE REVIEW THE ORDER';

                $this->saveLog(array(
                    'requestId' => $this->requestId,
                    'message' => $this->amountMismatchError
                ));
            } catch (\Exception $exception) {
                $exceptionMessage = sprintf(
                    "validateAmount exception[origin=%s][cartId=%s][merchantOrderId=%s][clearpayOrderId=%s][%s]",
                    $this->getOrigin(),
                    $this->merchantCartId,
                    $this->merchantOrderId,
                    $this->clearpayOrderId,
                    $exception->getMessage()
                );
                $this->saveLog(array('requestId' => $this->requestId, 'message' => $exceptionMessage));
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
            if ($this->merchantCart->orderExists() !== false) {
                $exceptionMessage = sprintf(
                    "Existing Order[origin=%s][cartId=%s][merchantOrderId=%s][clearpayOrderId=%s]",
                    $this->getOrigin(),
                    $this->merchantCartId,
                    $this->merchantOrderId,
                    $this->clearpayOrderId
                );
                throw new UnknownException($exceptionMessage);
            }

            // Double check
            $tableName = _DB_PREFIX_ . self::ORDERS_TABLE;
            $fieldName = 'ps_order_id';
            $sql = ('select ' . $fieldName . ' from `' . $tableName . '` where `id` = ' . (int)$this->merchantCartId
                . ' and `order_id` = \'' . $this->clearpayOrderId . '\''
                . ' and `token` = \'' . $this->token . '\''
                . ' and `' . $fieldName . '` is not null');
            $results = Db::getInstance()->ExecuteS($sql);
            if (is_array($results) && count($results) === 1) {
                $this->getMerchantOrderId();
                $exceptionMessage = sprintf(
                    "Order was already created [origin=%s][cartId=%s][merchantOrderId=%s][clearpayOrderId=%s]",
                    $this->getOrigin(),
                    $this->merchantCartId,
                    $this->merchantOrderId,
                    $this->clearpayOrderId
                );
                throw new UnknownException($exceptionMessage);
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
            $metadataOrder = $this->clearpayOrder->getMetadata();
            $metadataInfo = '';
            foreach ($metadataOrder as $metadataKey => $metadataValue) {
                if ($metadataKey == 'promotedProduct') {
                    $metadataInfo .= $metadataValue;
                }
            }

            $this->module->validateOrder(
                $this->merchantCartId,
                Configuration::get('PS_OS_PAYMENT'),
                $this->merchantCart->getOrderTotal(true),
                $this->productName,
                'clearpayOrderId: ' . $this->clearpayOrder->getId() . ' ' .
                'clearpayOrderStatus: '. $this->clearpayOrder->getStatus() .
                $this->amountMismatchError .
                $metadataInfo,
                array('transaction_id' => $this->clearpayOrderId),
                null,
                false,
                $this->config['secureKey']
            );
        } catch (\Exception $exception) {
            throw new UnknownException($exception->getMessage());
        }
        try {
            Db::getInstance()->update(
                self::ORDERS_TABLE,
                array('ps_order_id' => $this->module->currentOrder),
                'id = '. (int)$this->merchantCartId
                    . ' and order_id = \'' . $this->clearpayOrderId . '\''
                    . ' and token = \'' . $this->token . '\''
            );

        } catch (\Exception $exception) {
            $exceptionMessage = sprintf(
                "processMerchantOrder exception[origin=%s][cartId=%s][merchantOrderId=%s][clearpayOrderId=%s][%s]",
                $this->getOrigin(),
                $this->merchantCartId,
                $this->merchantOrderId,
                $this->clearpayOrderId,
                $exception->getMessage()
            );
            $this->saveLog(array('requestId' => $this->requestId, 'message' => $exceptionMessage));
        }
    }

    /**
     * Confirm the order in PAGANTIS
     *
     * @throws Exception
     */
    private function confirmClearpayOrder()
    {
        try {
            $this->orderClient->confirmOrder($this->clearpayOrderId);
            try {
                $mode = ($this->isPost()) ? 'NOTIFICATION' : 'REDIRECTION';
                $message = 'Order CONFIRMED. The order was confirmed by a ' . $mode .
                    '. Clearpay OrderId=' . $this->clearpayOrderId .
                    '. Prestashop OrderId=' . $this->module->currentOrder;
                $this->saveLog(array('requestId' => $this->requestId, 'message' => $message));
            } catch (\Exception $exception) {
                $exceptionMessage = sprintf(
                    "confirmClearpayOrder exception[origin=%s][cartId=%s][merchantOrderId=%s][clearpayOrderId=%s][%s]",
                    $this->getOrigin(),
                    $this->merchantCartId,
                    $this->merchantOrderId,
                    $this->clearpayOrderId,
                    $exception->getMessage()
                );
                $this->saveLog(array('requestId' => $this->requestId, 'message' => $exceptionMessage));
            }
        } catch (\Exception $exception) {
            throw new UnknownException(sprintf("[%s]%s", $this->getOrigin(), $exception->getMessage()));
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
            $this->getMerchantOrderId();
            $message = 'Roolback method: ' .
                '. Clearpay OrderId=' . $this->clearpayOrderId .
                '. Prestashop CartId=' . $this->merchantCartId .
                '. Prestashop OrderId=' . $this->merchantOrderId;
            if ($this->module->currentOrder) {
                $objOrder = new Order($this->module->currentOrder);
                $history = new OrderHistory();
                $history->id_order = (int)$objOrder->id;
                $history->changeIdOrderState(8, (int)($objOrder->id));
                $message .= ' Prestashop OrderId=' . $this->merchantCartId;
            }
            $this->saveLog(array(
                'requestId' => $this->requestId,
                'message' => $message
            ));
        } catch (\Exception $exception) {
            $this->saveLog(array(
                'requestId' => $this->requestId,
                'message' => $exception->getMessage()
            ));
        }
    }

    /**
     * Lock the concurrency to prevent duplicated inputs
     * @param $orderId
     *
     * @return bool
     * @throws UnknownException
     */
    protected function blockConcurrency($orderId)
    {
        try {
            $table = self::CART_TABLE;
            $insertBlock = Db::getInstance()->insert($table, array('id' =>(int)$orderId, 'timestamp' =>(time())));
            if ($insertBlock === false) {
                if ($this->isNotification()) {
                    throw new ConcurrencyException();
                } else {
                    $query = sprintf(
                        "SELECT TIMESTAMPDIFF(SECOND,NOW()-INTERVAL %s SECOND, FROM_UNIXTIME(timestamp)) 
                              as rest FROM %s WHERE %s",
                        self::CONCURRENCY_TIMEOUT,
                        _DB_PREFIX_.$table,
                        'id='.(int)$orderId
                    );
                    $resultSeconds = Db::getInstance()->getValue($query);
                    $restSeconds = isset($resultSeconds) ? ($resultSeconds) : 0;
                    $secondsToExpire = ($restSeconds>self::CONCURRENCY_TIMEOUT) ?
                        self::CONCURRENCY_TIMEOUT : $restSeconds;
                    if ($secondsToExpire > 0) {
                        sleep($secondsToExpire + 1);
                    }

                    $this->getMerchantOrderId();
                    $this->getClearpayOrderId();

                    $logMessage  = sprintf(
                        "User has waited %s seconds, default %s, bd time to expire %s [cartId=%s][origin=%s]",
                        $secondsToExpire,
                        self::CONCURRENCY_TIMEOUT,
                        $restSeconds,
                        $this->merchantCartId,
                        $this->getOrigin()
                    );

                    $this->saveLog(array('requestId' => $this->requestId, 'message' => $logMessage));

                    // After waiting...user continue the confirmation, hoping that previous call have finished.
                    return true;
                }
            }
        } catch (\Exception $exception) {
            throw new UnknownException($exception->getMessage());
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
                Db::getInstance()->delete(self::CART_TABLE, 'timestamp < ' . (time() - self::CONCURRENCY_TIMEOUT));
                return;
            }
            Db::getInstance()->delete(self::CART_TABLE, 'id = ' . (int)$orderId);
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
        $this->getMerchantOrderId();
        $data = array(
            'requestId' => $this->requestId,
            'merchantCartId' => $this->merchantCartId,
            'merchantOrderId' => $this->merchantOrderId,
            'clearpayOrderId' => $this->clearpayOrderId,
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
        $this->getMerchantOrderId();
        if ($this->isPost()) {
            $returnMessage = sprintf(
                "[origin=%s][cartId=%s][prestashopOrderId=%s][clearpayOrderId=%s][message=%s]",
                $this->getOrigin(),
                $this->merchantCartId,
                $this->merchantOrderId,
                $this->clearpayOrderId,
                $this->jsonResponse->getResult()
            );
            $this->saveLog(array('requestId' => $this->requestId, 'message' => $returnMessage));
            $this->jsonResponse->printResponse();
        } else {
            $parameters = array(
                'id_cart' => $this->merchantCartId,
                'key' => $this->config['secureKey'],
                'id_module' => $this->module->id,
                'id_order' => ($this->clearpayOrder) ? $this->clearpayOrder->getId() : null,
            );
            $url = ($error)? $this->config['urlKO'] : $this->config['urlOK'];
            $returnMessage = sprintf(
                "[origin=%s][cartId=%s][prestashopOrderId=%s][clearpayOrderId=%s][returnUrl=%s]",
                $this->getOrigin(),
                $this->merchantCartId,
                $this->merchantOrderId,
                $this->clearpayOrderId,
                $url
            );
            $this->saveLog(array('requestId' => $this->requestId, 'message' => $returnMessage));

            return $this->redirect($url, $parameters);
        }
    }

    /**
     * @return bool
     */
    private function isNotification()
    {
        return ($this->getOrigin() == 'Notification');
    }

    /**
     * @return bool
     */
    private function isRedirect()
    {
        return ($this->getOrigin() == 'Order');
    }

    /**
     * @return bool
     */
    private function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * @return bool
     */
    private function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }
}
