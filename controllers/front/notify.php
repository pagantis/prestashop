<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

/**
 * Class PaylaterNotifyModuleFrontController
 */
class PaylaterNotifyModuleFrontController extends ModuleFrontController
{
    /**
     * @var string $message
     */
    protected $message;

    /**
     * @var bool $error
     */
    protected $error = false;

    /**
     * Controller index method: validate and choose response type
     */
    public function postProcess()
    {
        //unlock cart_id locked for more than 10 seconds
        Db::getInstance()->delete('pmt_cart_process', 'timestamp < ' . (time() - 6));

        try {
            $this->processValidation();
        } catch (\Exception $exception) {
            $this->message = $exception->getMessage();
            $this->error = true;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->jsonResponse();
        } else {
            $this->redirect();
        }
    }

    /**
     * Send a jsonResponse
     */
    public function jsonResponse()
    {
        $cartId = Tools::getValue('id_cart');
        $secureKey = Tools::getValue('key');

        $result = json_encode(array(
            'timestamp' => time(),
            'order_id' => $cartId,
            'secure_key' => $secureKey,
            'result' => $this->message,
        ));

        if ($this->error) {
            header('HTTP/1.1 400 Bad Request', true, 400);
        } else {
            header('HTTP/1.1 200 Ok', true, 200);
        }

        header('Content-Type: application/json', true);
        header('Content-Length: ' . Tools::strlen($result));

        echo $result;
        exit();
    }

    /**
     * We receive a redirection
     *
     * we have to be sure that the payment has been successful for
     * the cart. So we will ask directly paga+tarde for it. Then if
     * true we will validate the order with the payment details.
     */
    public function redirect()
    {
        $cartId = Tools::getValue('id_cart');
        $secureKey = Tools::getValue('key');
        $cart = new Cart((int) $cartId);

        $query = array(
            'id_cart' => $cartId,
            'key' => $secureKey,
            'id_module' => $this->module->id,
            'id_order' => isset($cart) ? $cart->id : ''
        );

        if ($this->error) {
            //In case of error we can see the error in the header of the redirection:
            $redirectUrl = Configuration::get('pmt_url_ko');
            Tools::redirect($redirectUrl, null, null, 'ErrorMessage: '.$this->message);
        } else {
            $parsedUrl = parse_url(Configuration::get('pmt_url_ok'));
            $separator = ($parsedUrl['query'] == null) ? '?' : '&';
            $redirectUrl = Configuration::get('pmt_url_ok'). $separator .http_build_query($query);
            Tools::redirect($redirectUrl);
        }
    }

    /**
     *
     * Process validation vs API of pmt
     *
     * @throws PrestaShopDatabaseException
     * @throws \Httpful\Exception\ConnectionErrorException
     * @throws \PagaMasTarde\OrdersApiClient\Exception\HttpException
     * @throws \PagaMasTarde\OrdersApiClient\Exception\ValidationException
     */
    public function processValidation()
    {
        $cartId = Tools::getValue('id_cart');

        try {
            if (!Db::getInstance()->insert('pmt_cart_process', array('id' => $cartId, 'timestamp' => (time())))) {
                return;
            }
        } catch (\Exception $exception) {
            $this->message = 'PS Order currently under process, this is not bad, try again in 10 seconds';
            $this->error = true;
            return;
        }

        $secureKey = Tools::getValue('key');
        $privateKey = Configuration::get('pmt_private_key');
        $publicKey = Configuration::get('pmt_public_key');
        $cart = new Cart((int) $cartId);
        $pmtOrderId = Db::getInstance()->getValue(
            'select order_id from '._DB_PREFIX_.'pmt_order where id = '.$cartId
        );
        if ($secureKey && $cartId && Module::isEnabled('paylater')) {
            $orderClient = new \PagaMasTarde\OrdersApiClient\Client(
                $publicKey,
                $privateKey
            );
            $order = $orderClient->getOrder($pmtOrderId);
            $payed = in_array(
                $order->getStatus(),
                array(
                    \PagaMasTarde\OrdersApiClient\Model\Order::STATUS_AUTHORIZED,
                    \PagaMasTarde\OrdersApiClient\Model\Order::STATUS_CONFIRMED,
                )
            );
            if (!$payed) {
                $this->message = 'Order status is: ' . $order->getStatus();
                $this->error = true;
                return;
            }

            $totalAmount = $order->getShoppingCart()->getTotalAmount();
            if ($totalAmount != (int) ((string) (100 * $cart->getOrderTotal(true)))) {
                $this->triggerAmountPaymentError($cart, $cartId, $secureKey, $order);
                return;
            }

            if (Validate::isLoadedObject($cart)
            ) {
                if ($cart->orderExists() === false) {
                    /** @var PaymentModule $paymentModule */
                    $paymentModule = $this->module;
                    try {
                        $paymentModule->validateOrder(
                            $cartId,
                            Configuration::get('PS_OS_PAYMENT'),
                            $cart->getOrderTotal(true),
                            $this->module->displayName,
                            'pmtOrderId: ' . $order->getId(),
                            null,
                            null,
                            false,
                            $secureKey
                        );
                    } catch (\Exception $exception) {
                        $this->message = 'Internal saving exception: ' . $exception->getMessage();
                        $this->error = true;
                        return;
                    }
                    try {
                        $order = $orderClient->confirmOrder($pmtOrderId);
                    } catch (\Exception $exception) {
                        $this->message = 'Order confirmation exception: ' . $exception->getMessage();
                        $this->error = true;
                        return;
                    }
                    $this->message = 'Payment Validated and order status: ' . $order->getStatus();
                    return;
                }
                $this->message = 'Payment already Validated and order status: ' . $order->getStatus();
                return;
            }
            $this->message = 'PrestaShop Cart not found';
            $this->error = true;
            return;
        }
        $this->message = 'Bad request, module may not be enabled';
        $this->error = true;
    }

    /**
     * @param Cart $cart
     * @param $cartId
     * @param $secureKey
     * @param \PagaMasTarde\OrdersApiClient\Model\Order $order
     */
    public function triggerAmountPaymentError($cart, $cartId, $secureKey, $order)
    {
        if (Validate::isLoadedObject($cart)
        ) {
            if ($cart->orderExists() === false) {
                /** @var PaymentModule $paymentModule */
                $paymentModule = $this->module;
                try {
                    $paymentModule->validateOrder(
                        $cartId,
                        Configuration::get('PS_OS_ERROR'),
                        $cart->getOrderTotal(true),
                        $this->module->displayName,
                        'Error in amount, please conciliate manually with PMT backoffice',
                        null,
                        null,
                        false,
                        $secureKey
                    );
                } catch (\Exception $exception) {
                    $this->message = 'Internal saving exception: ' . $exception->getMessage();
                    $this->error = true;
                    return;
                }
                $this->message = 'Order amount not match and order status is: ' . $order->getStatus();
                return;
            }
            $this->message = 'Order already processed, amount not match and order status is: ' . $order->getStatus();
            return;
        }
    }
}
