<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
 * @license   proprietary
 */

require_once('AbstractController.php');

use Afterpay\SDK\HTTP\Request as ClearpayRequest;
use Afterpay\SDK\MerchantAccount as ClearpayMerchant;

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
     * @var Order $clearpayOrder
     */
    protected $clearpayOrder;

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
        // Validations
        try {
            $this->prepareVariables();
            $this->checkConcurrency();
            $this->getMerchantCart();
            $this->getClearpayOrderId();
            $this->getClearpayOrder();
            if ($this->checkOrderStatus()) {
                return $this->finishProcess(false);
            }
            $this->validateAmount();
            $this->checkMerchantOrderStatus();
        } catch (\Exception $exception) {
            return $this->cancelProcess($exception->getMessage());
        }

        // Proccess Clearpay Order
        try {
            $this->confirmClearpayOrder();
        } catch (\Exception $exception) {
            $this->rollbackMerchantOrder();
            return $this->cancelProcess($exception->getMessage());
        }

        // Process Merchant Order
        try {
            $this->processMerchantOrder();
        } catch (\Exception $exception) {
            return $this->cancelProcess($exception->getMessage());
        }

        try {
            $this->unblockConcurrency($this->merchantCartId);
        } catch (\Exception $exception) {
            $exceptionMessage = sprintf(
                "Clearpay module unblocking exception[cartId=%s][merchantOrderId=%s][clearpayOrderId=%s][%s]",
                $this->merchantCartId,
                $this->merchantOrderId,
                $this->clearpayOrderId,
                $exception->getMessage()
            );
            $this->saveLog($exceptionMessage, null, 2);
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
        $this->merchantOrderId =  $this->getMerchantCartId();
        if (!empty($this->merchantOrderId)) {
            $exceptionMessage = sprintf(
                "The order %s already exists in %s table",
                $this->merchantOrderId,
                self::ORDERS_TABLE
            );
            throw new \Exception($exceptionMessage);
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
        $this->token = Tools::getValue('token');
        $this->productName = "Clearpay";

        $this->config['publicKey'] = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
        $this->config['privateKey'] = Configuration::get('CLEARPAY_SANDBOX_SECRET_KEY');
        $this->config['environment'] = Configuration::get('CLEARPAY_ENVIRONMENT');

        if ($this->config['environment'] === 'production') {
            $this->config['publicKey'] = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            $this->config['privateKey'] = Configuration::get('CLEARPAY_PRODUCTION_SECRET_KEY');
        }

        $this->merchantCartId = Tools::getValue('id_cart');

        if ($this->merchantCartId == '') {
            throw new \Exception("Merchant cart id not provided in callback url");
        }

        if (!($this->config['secureKey'] && Module::isEnabled(self::CODE))) {
            // This exception is only for Prestashop
            throw new \Exception('Can\'t process Clearpay order, module may not be enabled');
        }
    }

    /**
     * Find prestashop Cart Id
     */
    public function getMerchantCartId()
    {
        $sql = 'select ps_order_id from ' . _DB_PREFIX_ .self::ORDERS_TABLE .' where id = '
            .(int)$this->merchantCartId . ' and token = \'' . $this->token . '\'';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Retrieve the merchant order by id
     *
     * @throws Exception
     */
    public function getMerchantCart()
    {
        try {
            $this->merchantCart = new Cart($this->merchantCartId);
            if (!Validate::isLoadedObject($this->merchantCart)) {
                // This exception is only for Prestashop
                throw new \Exception('Unable to load cart');
            }
            if ($this->merchantCart->secure_key != $this->config['secureKey']) {
                throw new \Exception('Secure Key is not valid');
            }
        } catch (\Exception $exception) {
            throw new \Exception('Unable to find cart with id' . $this->merchantCartId);
        }
    }

    /**
     * Find Clearpay Order Id
     *
     * @throws Exception
     */
    private function getClearpayOrderId()
    {
        $sql = 'select order_id from ' . _DB_PREFIX_.self::ORDERS_TABLE .' where id = '
            .(int)$this->merchantCartId . ' and token = \'' . $this->token . '\'';
        $this->clearpayOrderId= Db::getInstance()->getValue($sql);

        if (empty($this->clearpayOrderId)) {
            throw new \Exception("Clearpay order id not found on clearpay_orders table");
        }
    }

    /**
     * Find Clearpay Order in Orders Server using Clearpay SDK
     *
     * @throws Exception
     */
    private function getClearpayOrder()
    {
        $getOrderRequest = new ClearpayRequest();
        $clearpayMerchant = new ClearpayMerchant();
        $clearpayMerchant
            ->setMerchantId($this->config['publicKey'])
            ->setSecretKey($this->config['privateKey'])
            ->setApiEnvironment($this->config['environment'])
        ;
        $uri = $getOrderRequest->getUri();
        $getOrderRequest
            ->setMerchantAccount($clearpayMerchant)
            ->setUri($uri . '/' . $this->clearpayOrderId)
        ;
        $getOrderRequest->send();
        // $amount = $getOrderRequest->getResponse()->getParsedBody()->totalAmount->amount;
        $this->clearpayOrder = $getOrderRequest->getResponse()->getParsedBody();
        var_dump($uri . '/' . $this->clearpayOrderId, $this->clearpayOrder); die;
        if ($this->clearpayOrder) {

        }
    }

    /**
     * Compare statuses of merchant order and Clearpay order, witch have to be the same.
     *
     * @throws Exception
     */
    public function checkOrderStatus()
    {
        if ($this->clearpayOrder->getStatus() === ClearpayModelOrder::STATUS_CONFIRMED) {
            return true;
        }

        if ($this->clearpayOrder->getStatus() !== ClearpayModelOrder::STATUS_AUTHORIZED) {
            $status = '-';
            if ($this->clearpayOrder instanceof \Clearpay\OrdersApiClient\Model\Order) {
                $status = $this->clearpayOrder->getStatus();
            }
            throw new WrongStatusException($status);
        }
        return false;
    }

    /**
     * Check that the merchant order and the order in Clearpay have the same amount to prevent hacking
     *
     * @throws Exception
     */
    public function validateAmount()
    {
        $totalAmount = (string) $this->clearpayOrder->getShoppingCart()->getTotalAmount();
        $merchantAmount = (string) (100 * $this->merchantCart->getOrderTotal(true, Cart::BOTH));
        $merchantAmount = explode('.', explode(',', $merchantAmount)[0])[0];
        if ($totalAmount != $merchantAmount) {
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

            $amountMismatchError = '. Amount mismatch in PrestaShop Cart #'. $this->merchantCartId .
                ' compared with Clearpay Order: ' . $this->clearpayOrderId .
                '. The Cart in PrestaShop has an amount of ' . $psTotalAmount . ' and in Clearpay ' .
                $pgTotalAmount . ' PLEASE CONFIRM THE ORDER ON CLEARPAY MBO.';

            $this->saveLog($amountMismatchError, 3);
            throw new \Exception($amountMismatchError);
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
                    "Existing Order[cartId=%s][merchantOrderId=%s][clearpayOrderId=%s]",
                    $this->merchantCartId,
                    $this->merchantOrderId,
                    $this->clearpayOrderId
                );
                throw new \Exception($exceptionMessage);
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
                $exceptionMessage = sprintf(
                    "Order was already created [cartId=%s][Token=%s][clearpayOrderId=%s]",
                    $this->merchantCartId,
                    $this->token,
                    $this->clearpayOrderId
                );
                throw \Exception($exceptionMessage);
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
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
                $this->merchantCart->getOrderTotal(true, Cart::BOTH),
                $this->productName,
                'clearpayOrderId: ' . $this->clearpayOrder->getId() . ' ' .
                'clearpayOrderStatus: '. $this->clearpayOrder->getStatus() .
                $metadataInfo,
                array('transaction_id' => $this->clearpayOrderId),
                null,
                false,
                $this->config['secureKey']
            );
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
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
            $this->saveLog($exception->getMessage(), 2);
        }
    }

    /**
     * Confirm the order in Clearpay
     *
     * @throws Exception
     */
    private function confirmClearpayOrder()
    {
        try {
            $this->orderClient->confirmOrder($this->clearpayOrderId);
            try {
                $message = 'Order CONFIRMED. The order was confirmed' .
                    '. Clearpay OrderId=' . $this->clearpayOrderId .
                    '. Prestashop OrderId=' . $this->module->currentOrder;
                $this->saveLog($message, 1);
            } catch (\Exception $exception) {
                $exceptionMessage = sprintf(
                    "Confirm Clearpay Order exception[cartId=%s][merchantOrderId=%s][clearpayOrderId=%s][%s]",
                    $this->merchantCartId,
                    $this->merchantOrderId,
                    $this->clearpayOrderId,
                    $exception->getMessage()
                );
                $this->saveLog($exceptionMessage, 3);
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
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
            $message = 'Clearpay Roolback method called: ' .
                '. Clearpay OrderId=' . $this->clearpayOrderId .
                '. Prestashop CartId=' . $this->merchantCartId .
                '. Prestashop OrderId=' . $this->merchantOrderId;
            $this->saveLog($message, 3);
            if ($this->module->currentOrder) {
                $objOrder = new Order($this->module->currentOrder);
                $history = new OrderHistory();
                $history->id_order = (int)$objOrder->id;
                $history->changeIdOrderState(8, (int)($objOrder->id));
            }
        } catch (\Exception $exception) {
            $this->saveLog('Error on Clearpay rollback Transaction: ' .
                '. Clearpay OrderId=' . $this->clearpayOrderId .
                '. Prestashop CartId=' . $this->merchantCartId .
                '. Prestashop OrderId=' . $this->merchantOrderId .
                $exception->getMessage(), 3);
        }
    }

    /**
     * Lock the concurrency to prevent duplicated inputs
     * @param $orderId
     *
     * @throws Exception
     */
    protected function blockConcurrency($orderId)
    {
        try {
            $table = self::CART_TABLE;
            Db::getInstance()->insert($table, array('id' =>(int)$orderId, 'timestamp' =>(time())));
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param null $orderId
     *
     * @throws Exception
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
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * Do all the necessary actions to cancel the confirmation process in case of error
     * 1. Unblock concurrency
     * 2. Save log
     *
     * @param string $message
     * @return mixed
     */
    public function cancelProcess($message = '')
    {
        $this->saveLog($message, 2);
        return $this->finishProcess(true);
    }

    /**
     * Redirect the request to the e-commerce or show the output in json
     *
     * @param bool $error
     */
    public function finishProcess($error = true)
    {
        $this->getMerchantCartId();
        $parameters = array(
            'id_cart' => $this->merchantCartId,
            'key' => $this->config['secureKey'],
            'id_module' => $this->module->id,
            'id_order' => ($this->clearpayOrderId) ? $this->clearpayOrder->getId() : null,
        );
        $url = ($error)? $this->config['urlKO'] : $this->config['urlOK'];
        return $this->redirect($url, $parameters);
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
}
