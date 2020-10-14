<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 */
use Afterpay\SDK\HTTP\Request\CreateCheckout;
use Afterpay\SDK\Merchant as ClearpayMerchant;

require_once('AbstractController.php');

/**
 * Class ClearpayRedirectModuleFrontController
 */
class ClearpayPaymentModuleFrontController extends AbstractController
{
    /** @var string $language */
    protected $language;

    /**
     * Process Post Request
     *
     * @throws \Exception
     */
    public function postProcess()
    {
        $context = Context::getContext();

        /** @var Cart $cart */
        $cart = $context->cart;
        $shippingAddress = new Address($cart->id_address_delivery);
        $shippingCountryObj = new Country($shippingAddress->id_country);
        $shippingCountryCode = $shippingCountryObj->iso_code;
        $shippingStateObj = new State($shippingAddress->id_state);
        $shippingStateCode = '';
        if (!empty($shippingAddress->id_state) && !empty($state_object)) {
            $shippingStateCode = $shippingStateObj->iso_code;
        }

        $billingAddress = new Address($cart->id_address_invoice);
        $billingCountryCode = Country::getIsoById($billingAddress->id_country);
        $billingStateObj = new State($billingAddress->id_state);
        $billingStateCode = '';
        if (!empty($billingAddress->id_state) && !empty($state_object)) {
            $billingStateCode = $billingStateObj->iso_code;
        }

        $discountAmount = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        /** @var Carrier $carrier */
        $carrier = new Carrier($cart->id_carrier);

        /** @var Customer $customer */
        $customer = $context->customer;

        if (!$cart->id) {
            Tools::redirect('index.php?controller=order');
        }

        $urlToken = Tools::strtoupper(md5(uniqid(rand(), true)));

        $query = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );

        $koUrl = $context->link->getPageLink(
            'order',
            null,
            null,
            array('step'=>3)
        );
        $cancelUrl = (Clearpay::getExtraConfig('URL_KO') !== '') ? Clearpay::getExtraConfig('URL_KO', null) : $koUrl;

        $publicKey = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
        $secretKey = Configuration::get('CLEARPAY_SANDBOX_SECRET_KEY');
        $environment = Configuration::get('CLEARPAY_ENVIRONMENT');

        if ($environment === 'production') {
            $publicKey = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            $secretKey = Configuration::get('CLEARPAY_PRODUCTION_SECRET_KEY');
        }

        $okUrl = _PS_BASE_URL_SSL_.__PS_BASE_URI__
            .'index.php?canonical=true&fc=module&module=clearpay&controller=notify'
            .'&token='.$urlToken.http_build_query($query)
        ;

        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        $createCheckoutRequest = new CreateCheckout();
        $clearpayMerchant = new ClearpayMerchant();
        $clearpayMerchant
            ->setMerchantId($publicKey)
            ->setSecretKey($secretKey)
            ->setApiEnvironment($environment)
        ;
        $createCheckoutRequest
            ->setMerchant($clearpayMerchant)
            ->setMerchant(array(
                'redirectConfirmUrl' => $okUrl,
                'redirectCancelUrl' => $cancelUrl
            ))
            ->setTotalAmount(
                $this->parseAmount($cart->getOrderTotal(true, Cart::BOTH)),
                'EUR'
            )
            ->setTaxAmount(
                $this->parseAmount(
                    $cart->getOrderTotal(true, Cart::BOTH) - $cart->getOrderTotal(false, Cart::BOTH)
                ),
                'EUR'
            )
            ->setConsumer(array(
                'phoneNumber' => $billingAddress->phone,
                'givenNames' => $customer->firstname,
                'surname' => $customer->lastname,
                'email' => $customer->email
            ))
            ->setBilling(array(
                'name' => $billingAddress->firstname . " " . $billingAddress->lastname,
                'line1' => $billingAddress->address1,
                'line2' => $billingAddress->address2,
                'suburb' => $billingAddress->city,
                'state' => $billingStateCode,
                'postcode' => $billingAddress->postcode,
                'countryCode' => $billingCountryCode,
                'phoneNumber' => $billingAddress->phone
            ))
            ->setShipping(array(
                'name' => $shippingAddress->firstname . " " . $shippingAddress->lastname,
                'line1' => $shippingAddress->address1,
                'line2' => $shippingAddress->address2,
                'suburb' => $shippingAddress->city,
                'state' => $shippingStateCode,
                'postcode' => $shippingAddress->postcode,
                'countryCode' => $shippingCountryCode,
                'phoneNumber' => $shippingAddress->phone
            ))
            ->setShippingAmount(
                $this->parseAmount($cart->getTotalShippingCost()),
                'EUR'
            );

        if (!empty($discountAmount)) {
            $createCheckoutRequest->setDiscounts(array(
                array(
                    'displayName' => 'Clearpay Discount coupon',
                    'amount' => array($this->parseAmount($discountAmount), 'EUR')
                )
            ));
        }

        $items = $cart->getProducts();
        $products = array();
        foreach ($items as $key => $item) {
            $products[] = array(
                'name' => $item['name'],
                'sku' => $item['reference'],
                'quantity' => $item['quantity'],
                'price' => array(
                    $this->parseAmount($item['price_wt']),
                    'EUR'
                )
            );
        }
        $createCheckoutRequest->setItems($products);

        $header = $this->module->name . '/' . $this->module->version
            . '(Prestashop/' . _PS_VERSION_ . '; PHP/' . phpversion() . '; Merchant/' . $publicKey
            . ' ' . _PS_BASE_URL_SSL_.__PS_BASE_URI__;
        $createCheckoutRequest->addHeader('User-Agent', $header);

//        $createCheckoutRequest->setCourier(array(
//            'shippedAt' => '2019-01-01T00:00:00+10:00',
//            'name' => 'Australia Post',
//            'tracking' => 'AA0000000000000',
//            'priority' => 'STANDARD'
//        ))

        $url = '';
        if ($createCheckoutRequest->isValid()) {
            $createCheckoutRequest->send();
            if (isset($createCheckoutRequest->getResponse()->getParsedBody()->errorCode)) {
                $this->saveLog($createCheckoutRequest->getResponse()->getParsedBody()->message);
                $url = 'ko2';
            }
            $url = 'ok';
            var_dump($createCheckoutRequest->getResponse()->getParsedBody()->message);
        } else {
            $this->saveLog($createCheckoutRequest->getValidationErrors());
            $url = 'ko2';
        }

        die($url);
        Tools::redirect($url);
    }

    public function parseAmount($amount = null)
    {
        return number_format(
            round($amount, 2, PHP_ROUND_HALF_UP),
            2,
            '.',
            ''
        );
    }
}
