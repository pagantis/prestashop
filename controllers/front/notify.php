<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

use PagaMasTarde\PmtApiClient;

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
            Db::getInstance()->delete('pmt_cart_process', 'id = ' . Tools::getValue('id_cart'));
            Db::getInstance()->delete('pmt_cart_process', 'timestamp < ' . time() - 10);

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
    public function processValidation()
    {
        $cartId = Tools::getValue('id_cart');

        if (!Db::getInstance()->insert('pmt_cart_process', array('id' => $cartId))) {
            return; //occupied, continue please.
        }

        $secureKey = Tools::getValue('key');
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $privateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'. $paylaterMode);
        $cart = new Cart((int) $cartId);

        if ($secureKey && $cartId && Module::isEnabled('paylater')) {
            $pmtClient = new PmtApiClient($privateKey);
            $payed = $pmtClient->charge()->validatePaymentForOrderId($cartId);
            if (!$payed) {
                $this->message = 'Payment not existing in PMT';
                $this->error = true;
                return;
            }

            $payments = $pmtClient->charge()->getChargesByOrderId($cartId);
            $latestCharge = array_shift($payments);
            if ($latestCharge->getAmount() != intval(strval(100 * $cart->getOrderTotal(true)))) {
                $this->message = 'Amount to pay mismatch';
                $this->error = true;
                return;
            }

            if (Validate::isLoadedObject($cart)
            ) {
                if ($cart->orderExists() === false) {
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
