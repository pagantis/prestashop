<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

use PagaMasTarde\PmtApiClient;
use \PagaMasTarde\Model\Charge;

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
    protected function jsonResponse()
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

        exit($result);
    }

    /**
     * We receive a redirection
     *
     * we have to be sure that the payment has been successful for
     * the cart. So we will ask directly paga+tarde for it. Then if
     * true we will validate the order with the payment details.
     */
    protected function redirect()
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
            $link = $this->context->link;
            $redirectUrl = $link->getPageLink('order');
            Tools::redirect($redirectUrl, null, null, 'ErrorMessage: '.$this->message);
        } else {
            $link = $this->context->link;
            $redirectUrl = $link->getPageLink('order-confirmation', null, null, $query);
            Tools::redirect($redirectUrl);
        }
    }

    /**
     * Process validation vs API of pmt
     */
    protected function processValidation()
    {
        $cartId = Tools::getValue('id_cart');
        $secureKey = Tools::getValue('key');
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = PAYLATER_PROD_STATUS[(int) $paylaterProd];
        $privateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'. $paylaterMode);

        if ($secureKey && $cartId && Module::isEnabled('paylater')) {
            $pmtClient = new PmtApiClient($privateKey);
            $charge = $pmtClient->charge()->getChargeByOrderId($cartId);
            if (!$charge instanceof Charge || $charge->getPaid() !== true) {
                $this->message = 'Payment not existing in PMT';
                $this->error = true;
                return;
            }

            $cart = new Cart((int) $cartId);
            if (Validate::isLoadedObject($cart)
            ) {
                if ($cart->OrderExists() == false) {
                    /** @var PaymentModule $paymentModule */
                    $paymentModule = $this->module;
                    $paymentModule->validateOrder(
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
                    $this->message = 'Payment Validated';
                    return;
                }
                $this->message = 'Payment already Validated';
                return;
            }
            $this->message = 'Order not found';
            $this->error = true;
            return;
        }
        $this->message = 'Bad request';
        $this->error = true;
    }
}
