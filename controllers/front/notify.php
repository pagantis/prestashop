<?php

require_once('AbstractController.php');

use PagaMasTarde\OrdersApiClient\Client as PmtClient;
use PagaMasTarde\OrdersApiClient\Model\Order as PmtModelOrder;

/**
 * Class PaylaterNotifyModuleFrontController
 */
class PaylaterNotifyModuleFrontController extends AbstractController
{

    /**
     * @var string $merchantOrderId
     */
    protected $merchantOrderId;

    /**
     * @var Mage_Sales_Model_Order $merchantOrder
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
     * Main action of the controller. Dispatch the Notify process
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
            return $this->cancelProcess($exception);
        }

        try {
            $this->confirmPmtOrder();
        } catch (\Exception $exception) {
            $this->rollbackMerchantOrder();
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
     * Find and init variables needed to process payment
     *
     * @throws Exception
     */
    public function prepareVariables()
    {
        $this->merchantOrderId = Tools::getValue('id_cartita');
        if ($this->merchantOrderId == '') {
            throw new \Exception(self::CC_NO_MERCHANT_ORDERID, 404);
        }

        try {
            $this->config = array(
                'urlOK' => parse_url(Configuration::get('pmt_url_ok')),
                'urlKO' => Configuration::get('pmt_url_ko'),
                'publicKey' => Configuration::get('pmt_public_key'),
                'privateKey' => Configuration::get('pmt_private_key'),
                'secureKey' => Tools::getValue('key'),
            );
        } catch (\Exception $exception) {
            throw new \Exception(self::CC_NO_CONFIG, 500);
        }

        if (!($this->config['secureKey'] && $this->merchantOrderId && Module::isEnabled(self::PMT_CODE))) {
            throw new \Exception(self::CC_MALFORMED, 500);
        }
    }

    /**
     * Check the concurrency of the purchase
     *
     * @throws Exception
     */
    public function checkConcurrency()
    {
        try {
            $this->prepareVariables();
            $this->unblockConcurrency();
            $this->blockConcurrency($this->merchantOrderId);
        } catch (\Exception $exception) {
            $this->statusCode = 429;
            $this->errorMessage = self::CC_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
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
            /** @var Mage_Sales_Model_Order $order */
            $this->merchantOrder = new Cart($this->merchantOrderId);
            if (!Validate::isLoadedObject($this->merchantOrder)) {
                throw new \Exception(self::GMO_CART_NOT_LOADED, 500);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 404;
            $this->errorMessage = self::GMO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
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
                throw new \Exception(self::GPOI_NO_ORDERID, 404);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 404;
            $this->errorMessage = self::GPOI_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    /**
     * Find PMT Order in Orders Server using PagaMasTarde\OrdersApiClient
     *
     * @throws Exception
     */
    private function getPmtOrder()
    {
        try {
            $this->orderClient = new PmtClient($this->config['publicKey'], $this->config['privateKey']);
            $this->pmtOrder = $this->orderClient->getOrder($this->pmtOrderId);
            if (!($this->pmtOrder instanceof PmtModelOrder)) {
                throw new \Exception(self::GPO_ERR_TYPEOF, 500);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 400;
            $this->errorMessage = self::GPO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    /**
     * Compare statuses of merchant order and PMT order, witch have to be the same.
     *
     * @throws Exception
     */
    public function checkOrderStatus()
    {
        try {
            if ($this->pmtOrder->getStatus() !== PmtModelOrder::STATUS_AUTHORIZED) {
                throw new \Exception(self::COS_WRONG_STATUS, 403);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 403;
            $this->errorMessage = self::COS_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
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
                throw new \Exception(self::CMOS_WRONG_CURRENT_STATUS, 409);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 409;
            $this->errorMessage = self::CMOS_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    /**
     * Check that the merchant order and the order in PMT have the same amount to prevent hacking
     *
     * @throws Exception
     */
    public function validateAmount()
    {
        try {
            $totalAmount = $this->pmtOrder->getShoppingCart()->getTotalAmount();
            if ($totalAmount != (int)((string) (100 * $this->merchantOrder->getOrderTotal(true)))) {
                throw new \Exception(self::VA_WRONG_AMOUNT, 409);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 409;
            $this->errorMessage = self::VA_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
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
                null,
                null,
                false,
                $this->config['secureKey']
            );

        } catch (\Exception $exception) {
            $this->statusCode = 500;
            $this->errorMessage = self::PMO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
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
            $this->statusCode = 500;
            $this->errorMessage = self::CPO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    /**
     * Leave the merchant order as it was peviously
     *
     * @throws Exception
     */
    public function rollbackMerchantOrder()
    {
        // Do nothing.
    }


    /**
     * Lock the concurrency to prevent duplicated inputs
     *
     * @param $orderId
     * @throws Exception
     */
    protected function blockConcurrency($orderId)
    {
        Db::getInstance()->insert('pmt_cart_process', array('id' => $orderId, 'timestamp' => (time())));
    }

    /**
     * Unlock the concurrency
     *
     * @throws Exception
     */
    protected function unblockConcurrency()
    {
        Db::getInstance()->delete('pmt_cart_process', 'timestamp < ' . (time() - 6));
    }

    /**
     * Do all the necessary actions to cancel the confirmation process in case of error
     * 1. Unblock concurrency
     * 3. Save log
     *
     * @param Exception $exception
     * @return Mage_Core_Controller_Response_Http|Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function cancelProcess(\Exception $exception)
    {
        if ($this->merchantOrder) {
            $this->module->validateOrder(
                $this->merchantOrderId,
                Configuration::get('PS_OS_ERROR'),
                $this->merchantOrder->getOrderTotal(true),
                $this->module->displayName,
                'pmtOrderId: ' . $this->pmtOrder->getId(),
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
            'pmtCode' => $this->statusCode,
            'pmtMessage' => $this->errorMessage,
            'pmtMessageDetail' => $this->errorDetail,
            'pmtOrderId' => $this->pmtOrderId,
            'merchantOrderId' => $this->merchantOrderId,
            'method' => $method,
            'line' => $line,
        ));
        return $this->finishProcess(true);
    }

    /**
     * Redirect the request to the e-commerce or show the output in json
     *
     * @param bool $error
     * @return Mage_Core_Controller_Response_Http|Mage_Core_Controller_Varien_Action
     */
    public function finishProcess($error = true)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return $this->response();
        }

        $parameters = array(
            'id_cart' => $this->merchantOrderId,
            'key' => $this->config['secureKey'],
            'id_module' => $this->module->id,
            'id_order' => $this->merchantOrder->id,
        );
        $url = ($error)? $this->config['urlKO'] : $this->config['urlOK'];
        return $this->redirect($error, $url, $parameters);
    }
}
