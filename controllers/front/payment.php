<?php

use ShopperLibrary\ObjectModule\PrestashopObjectModule;
use ShopperLibrary\ShopperClient;

/**
 * Class AplazameRedirectModuleFrontController
 */
class PaylaterPaymentModuleFrontController extends ModuleFrontController
{
    /** Directory PATH of the module */
    const _PS_PAYLATER_DIR = _PS_MODULE_DIR_.'paylater/';

    /**
     * Array of possible status of variable PAYLATER_PROD
     */
    const PAYLATER_PROD_STATUS = [
        0 => 'TEST',
        1 => 'PROD'
    ];

    /**
     * Process Post Request
     */
    public function postProcess()
    {
        /** @var Cart $cart */
        $cart = $this->context->cart;

        if (!$cart->id) {
            Tools::redirect('index.php?controller=order');
        }

        /** @var Customer $customer */
        $customer = $this->context->customer;
        $link = $this->context->link;
        $query = [
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        ];

        $discount = Configuration::get('PAYLATER_DISCOUNT');
        $currency = new Currency($cart->id_currency);
        $callbackUrl = $link->getModuleLink('paylater', 'notify', $query);
        $cancelUrl = $link->getPageLink('order');
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = self::PAYLATER_PROD_STATUS[(int) $paylaterProd];
        $paylaterPublicKey = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterPrivateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'.$paylaterMode);
        $iframe = Configuration::get('PAYLATER_IFRAME');
        $includeSimulator = Configuration::get('PAYLATER_ADD_SIMULATOR');
        $okUrl =  $link->getPageLink('order-confirmation', null, null, $query);
        $koUrl = $link->getPageLink('order');
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign('iframe', $iframe);

        $prestashopObjectModule = new PrestashopObjectModule();
        $prestashopObjectModule->requiredConfig
            ->setPublicKey($paylaterPublicKey)
            ->setPrivateKey($paylaterPrivateKey)
            ->setCurrency($currency->iso_code)
            ->setOrderId($cart->id)
            ->setAmount((int) ($cart->getOrderTotal() * 100))
            ->setCancelUrl($cancelUrl)
            ->setDiscount($discount)
            ->setIFrame($iframe)
            ->setCallbackUrl($callbackUrl)
            ->setOkUrl($okUrl)
            ->setKoUrl($koUrl)
            ->setName($customer->firstname.' '.$customer->lastname)
            ->setEmail($customer->email)
        ;

        $prestashopObjectModule->customerConfig
            ->setBirthDate(new \DateTime(date('y-m-d', $customer->birthday)))
            ->setIsCustomer($customer->isGuest())
            ->setGender($customer->id_gender)
            ->setMemberSince(new \DateTime(date('y-m-d', $customer->date_add)))
        ;

        $prestashopObjectModule->setIncludeSimulator($includeSimulator);
        $prestashopObjectModule->setCart($cart);
        $prestashopObjectModule->setCustomer($customer);

        $shopperClient = new ShopperClient('http://shopper.localhost/prestashop/');
        $shopperClient->setObjectModule($prestashopObjectModule);
        $paymentForm = $shopperClient->getPaymentForm();
        $paymentForm = json_decode($paymentForm);

        //print_r($paymentForm);
        //die;

        $this->context->smarty->assign([
            'form' => $paymentForm->data->form,
        ]);

        if (_PS_VERSION_ < 1.7) {
            $this->setTemplate('payment-15.tpl');
        } else {
            $this->setTemplate('module:paylater/views/templates/front/payment-17.tpl');
        }
    }

    /**
     * @param Cart $cart
     *
     * @return array
     */
    private function getButtonTemplateVars(Cart $cart)
    {
        $currency = new Currency(($cart->id_currency));

        return array(
            'paylater_button' => '#paylater_payment_button',
            'paylater_currency_iso' => $currency->iso_code,
            'paylater_cart_total' => $cart->getOrderTotal(),
        );
    }
}
