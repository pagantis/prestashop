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
        $cancelUrl = $link->getPageLink('order', null, null, array('step'=>3));
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $paylaterPublicKey = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterPrivateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'.$paylaterMode);
        $iframe = Configuration::get('PAYLATER_IFRAME');
        $includeSimulator = Configuration::get('PAYLATER_ADD_SIMULATOR');
        $canonicalUrl = Configuration::get('PAYLATER_NOTIFY_URL');
        $okUrl = $link->getModuleLink('paylater', 'notify', $query);
        if ($canonicalUrl) {
            $okUrl = _PS_BASE_URL_.__PS_BASE_URI__
                     .'index.php?canonical=true&fc=module&module=paylater&controller=notify&'
                     .http_build_query($query)
            ;
        }

        $shippingAddress = new Address($cart->id_address_delivery);
        $billingAddress = new Address($cart->id_address_invoice);
        $discount = Configuration::get('PAYLATER_DISCOUNT');
        $link = Tools::getHttpHost(true).__PS_BASE_URI__;
        $spinner = $link . ('modules/paylater/views/img/spinner.gif');
        $css = 'https://shopper.pagamastarde.com/css/paylater-modal.min.css';
        $prestashopCss = 'https://shopper.pagamastarde.com/css/paylater-prestashop.min.css';

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

        $shopperClient = new \ShopperLibrary\ShopperClient(PAYLATER_SHOPPER_URL);
        $shopperClient->setObjectModule($prestashopObjectModule);
        $response = $shopperClient->getPaymentForm();
        $url    = "";
        if ($response) {
            $paymentForm = json_decode($response);
            if (is_object($paymentForm) && is_object($paymentForm->data)) {
                $url    = $paymentForm->data->url;
            }
        }

        if (!$iframe) {
            Tools::redirect($url);
        }
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'url'           => $url,
            'spinner'       => $spinner,
            'css'           => $css,
            'prestashopCss' => $prestashopCss,
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
