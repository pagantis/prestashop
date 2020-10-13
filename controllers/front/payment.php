<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 */
use Afterpay\SDK\HTTP\Request\CreateCheckout;

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

        $discountAmount = $this->cart_object->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        /** @var Carrier $carrier */
        $carrier = new Carrier($cart->id_carrier);

        /** @var Customer $customer */
        $customer = $context->customer;

        if (!$cart->id) {
            Tools::redirect('index.php?controller=order');
        }

        $urlToken = strtoupper(md5(uniqid(rand(), true)));

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
        $privateKey = Configuration::get('CLEARPAY_SANDBOX_SECRET_KEY');
        $environment = Configuration::get('CLEARPAY_ENVIRONMENT');

        if ($environment === 'production') {
            $publicKey = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            $privateKey = Configuration::get('CLEARPAY_PRODUCTION_SECRET_KEY');
        }

        $okUrl = _PS_BASE_URL_SSL_.__PS_BASE_URI__
            .'index.php?canonical=true&fc=module&module=clearpay&controller=notify'
            .'&token='.$urlToken.http_build_query($query)
        ;

        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        $createCheckoutRequest = new CreateCheckout();
        $createCheckoutRequest
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
        $promotedAmount = 0;
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

//        $createCheckoutRequest->setCourier(array(
//            'shippedAt' => '2019-01-01T00:00:00+10:00',
//            'name' => 'Australia Post',
//            'tracking' => 'AA0000000000000',
//            'priority' => 'STANDARD'
//        ))

        if ($createCheckoutRequest->isValid()) {
            $createCheckoutRequest->send();

            echo $createCheckoutRequest->getRawLog();
        } else {
            echo $createCheckoutRequest->getValidationErrorsAsHtml();
        }

        $url = '';
        try {
            $orderClient = new \Pagantis\OrdersApiClient\Client(
                trim($publicKey),
                trim($privateKey)
            );
            $order = $orderClient->createOrder($order);

            if ($order instanceof \Pagantis\OrdersApiClient\Model\Order) {
                $url = $order->getActionUrls()->getForm();
                /** @var string $orderId MD5 value */
                $orderId = $order->getId();
                $sql = "INSERT INTO `" . _DB_PREFIX_ . "clearpay_order` (`id`, `order_id`, `token`)
                     VALUES ('$cart->id','$orderId', '$urlToken')";
                $result = Db::getInstance()->execute($sql);
                if (!$result) {
                    throw new UnknownException('Unable to save clearpay-order-id in database: '. $sql);
                }
            } else {
                throw new OrderNotFoundException();
            }
        } catch (\Exception $exception) {
            $this->saveLog(array(), $exception, 2);
            Tools::redirect($cancelUrl);
        }

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
