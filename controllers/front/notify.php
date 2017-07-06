<?php

use PagaMasTarde\PmtApiClient;
use \PagaMasTarde\Model\Charge;

/**
 * Class PaylaterNotifyModuleFrontController
 */
class PaylaterNotifyModuleFrontController extends ModuleFrontController
{
    /**
     * @param $message
     * @param $error
     */
    protected function response($message, $error = false)
    {
        $cartId = Tools::getValue('id_cart');
        $secureKey = Tools::getValue('key');

        $result = json_encode([
            'timestamp' => time(),
            'order_id' => $cartId,
            'secure_key' => $secureKey,
            'result' => $message,
        ]);

        if ($error) {
            header('HTTP/1.1 400 Bad Request', true, 400);

        } else {
            header('HTTP/1.1 200 Ok', true, 200);
        }

        header('Content-Type: application/json', true);
        header('Content-Length: ' . strlen($result));

        exit($result);
    }

    /**
     * Validate if the request is a notification or nor:
     */
    public function postProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $message = json_encode($this->processNotification());
        } else {
            $message = json_encode($this->processRedirection());
        }
        header('HTTP/1.1 200 Ok', true, 200);
        header('Content-Type: application/json', true);
        header('Content-Length: ' . strlen($message));

        exit($message);
    }

    /**
     * We receive a notification:
     *
     * we have to handle it, change the order status if all is correct
     * then we can inform paga+tarde that all worked fine on our side.
     */
    protected function processNotification()
    {
        if (!Module::isEnabled('paylater')) {
            $this->response('Paylater is not enabled', true);
        }
        $cartId = Tools::getValue('id_cart');
        $secureKey = Tools::getValue('key');

        if (!$secureKey) {
            $this->response('Missing required fields', true);
        }

        try {
            $cart = new Cart((int) $cartId);
            if (!Validate::isLoadedObject($cart)) {
                $this->response('Cart does not exists or does not have an order', true);
            }

            $this->module->validateOrder(
                $cartId,
                Configuration::get('PS_OS_PAYMENT'),
                $cart->getOrderTotal(true),
                $this->module->displayName,
                null,
                null,
                null,
                false,
                $secureKey
            );
        } catch (\Exception $e) {
            $this->response('Cart does not exists or does not have an order', true);
        }

        return 'Payment Validated';
    }

    /**
     * We receive a redirection
     *
     * we have to be sure that the payment has been successful for
     * the cart. So we will ask directly paga+tarde for it. Then if
     * true we will validate the order with the payment details.
     */
    protected function processRedirection()
    {
        $cartId = Tools::getValue('id_cart');
        $secureKey = Tools::getValue('key');
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = PAYLATER_PROD_STATUS[(int) $paylaterProd];
        $privateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'. $paylaterMode);

        if ($secureKey && $cartId && Module::isEnabled('paylater')) {
            $pmtClient = new PmtApiClient($privateKey);
            $charge = $pmtClient->charge()->getChargeByOrderId($cartId);
            $cart = new Cart((int) $cartId);
            if (Validate::isLoadedObject($cart) &&
                $cart->OrderExists() == false &&
                $charge instanceof Charge &&
                $charge->getPaid() === true
            ) {
                $this->module->validateOrder(
                    $cartId,
                    Configuration::get('PS_OS_PAYMENT'),
                    $cart->getOrderTotal(true),
                    $this->module->displayName,
                    null,
                    null,
                    null,
                    false,
                    $secureKey
                );

                $query = [
                    'id_cart' => $cartId,
                    'key' => $secureKey,
                    'id_module' => $this->module->id,
                    'id_order' => isset($cart) ? $cart->id : ''
                ];
                $link = $this->context->link;
                $redirectUrl = $link->getPageLink('order-confirmation', null, null, $query);
                Tools::redirect($redirectUrl);
            }
        }

        $link = $this->context->link;
        $redirectUrl = $link->getPageLink('checkout');
        Tools::redirect($redirectUrl);
    }
}
