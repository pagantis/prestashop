<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

/**
 * Class PaylaterRedirectModuleFrontController
 */
class PaylaterPaymentModuleFrontController extends ModuleFrontController
{
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
        $query = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );

        $currency = new Currency($cart->id_currency);
        $currencyIso = $currency->iso_code;
        $cancelUrl = $link->getPageLink('order');
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = PAYLATER_PROD_STATUS[(int) $paylaterProd];
        $paylaterPublicKey = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterPrivateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'.$paylaterMode);
        $iframe = Configuration::get('PAYLATER_IFRAME');
        $includeSimulator = Configuration::get('PAYLATER_ADD_SIMULATOR');
        $okUrl = $link->getModuleLink('paylater', 'notify', $query);
        $shippingAddress = new Address($cart->id_address_delivery);
        $billingAddress = new Address($cart->id_address_invoice);
        $discount = Configuration::get('PAYLATER_DISCOUNT');
        $spinner = _PS_PAYLATER_STATIC. '/views/img/spinner.gif';
        $css = _PS_PAYLATER_STATIC. '/views/css/paylater.css';

        $prestashopObjectModule = new \ShopperLibrary\ObjectModule\PrestashopObjectModule();
        $prestashopObjectModule
            ->setPublicKey($paylaterPublicKey)
            ->setPrivateKey($paylaterPrivateKey)
            ->setCurrency($currencyIso)
            ->setDiscount($discount)
            ->setOkUrl($okUrl)
            ->setNokUrl($cancelUrl)
            ->setIFrame($iframe)
            ->setCallbackUrl($okUrl)
            ->setCancelledUrl($cancelUrl)
            ->setIncludeSimulator($includeSimulator)
            ->setCart(CartExport::export($cart))
            ->setCustomer(CustomerExport::export($customer))
            ->setPsShippingAddress(AddressExport::export($shippingAddress))
            ->setPsBillingAddress(AddressExport::export($billingAddress))
            ->setMetadata(array(
                'ps' => _PS_VERSION_,
                'pmt' => $this->module->version,
                'php' => phpversion(),
            ))
        ;

        $shopperClient = new \ShopperLibrary\ShopperClient(PAYLATER_SHOPPER_DEMO_URL);
        $shopperClient->setObjectModule($prestashopObjectModule);
        $paymentForm = $shopperClient->getPaymentForm();
        $paymentForm = json_decode($paymentForm);

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'form'          => $paymentForm->data->form,
            'spinner'       => $spinner,
            'iframe'        => $iframe,
            'css'           => $css,
            'checkoutUrl'   => $cancelUrl,
        ));

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
