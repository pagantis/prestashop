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

        $iframe = Configuration::get('PAYLATER_IFRAME');
        $cancelUrl = $link->getPageLink('order', null, null, array('step'=>3));
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $paylaterPublicKey = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterPrivateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'.$paylaterMode);
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
        $metadata = array(
            'ps' => _PS_VERSION_,
            'pmt' => $this->module->version,
            'php' => phpversion(),
        );

        try {
            $orderShippingAddress =  new \PagaMasTarde\OrdersApiClient\Model\Order\User\Address();
            $orderShippingAddress
                ->setZipCode($shippingAddress->postcode)
                ->setFullName($shippingAddress->firstname . ' ' . $shippingAddress->lastname)
                ->setCountryCode('ES')
                ->setCity($shippingAddress->city)
                ->setAddress($shippingAddress->address1 . ' ' . $shippingAddress->address2)
            ;

            $orderBillingAddress = new \PagaMasTarde\OrdersApiClient\Model\Order\User\Address();
            $orderBillingAddress
                ->setZipCode($billingAddress->postcode)
                ->setFullName($billingAddress->firstname . ' ' . $billingAddress->lastname)
                ->setCountryCode('ES')
                ->setCity($billingAddress->city)
                ->setAddress($billingAddress->address1 . ' ' . $billingAddress->address2)
            ;

            $orderUser = new \PagaMasTarde\OrdersApiClient\Model\Order\User();
            $orderUser
                ->setAddress($orderShippingAddress)
                ->setFullName($orderShippingAddress->getFullName())
                ->setBillingAddress($orderBillingAddress)
                ->setDateOfBirth($customer->birthday)
                ->setEmail($this->context->cookie->logged ? $this->context->cookie->email : $customer->email)
                ->setFixPhone($shippingAddress->phone)
                ->setMobilePhone($shippingAddress->phone_mobile)
                ->setShippingAddress($orderShippingAddress)
            ;

            $orders = Order::getCustomerOrders($customer->id);
            /** @var \PrestaShop\PrestaShop\Adapter\Entity\Order $order */
            foreach ($orders as $order) {
                if ($order['valid']) {
                    $orderHistory = new \PagaMasTarde\OrdersApiClient\Model\Order\User\OrderHistory();
                    $orderHistory
                        ->setAmount(intval(100 * $order['total_paid']))
                        ->setDate(new \DateTime($order['date_add']))
                    ;
                    $orderUser->addOrderHistory($orderHistory);
                }
            }

            if (\PagaMasTarde\OrdersApiClient\Model\Order\User::dniCheck($shippingAddress->dni)) {
                $orderUser->setDni($shippingAddress->dni);
            }

            $details = new \PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart\Details();
            $details->setShippingCost(intval(strval(100 * $cart->getTotalShippingCost())));
            $items = $cart->getProducts();
            foreach ($items as $key => $item) {
                $product = new \PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart\Details\Product();
                $product
                    ->setAmount(intval(100 * $item['price_wt']))
                    ->setQuantity($item['quantity'])
                    ->setDescription($item['name']);
                $details->addProduct($product);
            }

            $orderShoppingCart = new \PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart();
            $orderShoppingCart
                ->setDetails($details)
                ->setOrderReference($cart->id)
                ->setPromotedAmount(0)
                ->setTotalAmount(intval(strval(100 * $cart->getOrderTotal(true))))
            ;

            $orderConfigurationUrls = new \PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Urls();
            $orderConfigurationUrls
                ->setCancel($cancelUrl)
                ->setKo($cancelUrl)
                ->setNotificationCallback($okUrl)
                ->setOk($okUrl)
            ;

            $orderChannel = new \PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Channel();
            $orderChannel
                ->setAssistedSale(false)
                ->setType(PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Channel::ONLINE)
            ;

            $orderConfiguration = new \PagaMasTarde\OrdersApiClient\Model\Order\Configuration();
            $orderConfiguration
                ->setChannel($orderChannel)
                ->setUrls($orderConfigurationUrls)
            ;

            $metadataOrder = new \PagaMasTarde\OrdersApiClient\Model\Order\Metadata();
            foreach ($metadata as $key => $metadatum) {
                $metadataOrder
                    ->addMetadata($key, $metadatum);
            }

            $order = new \PagaMasTarde\OrdersApiClient\Model\Order();
            $order
                ->setConfiguration($orderConfiguration)
                ->setMetadata($metadataOrder)
                ->setShoppingCart($orderShoppingCart)
                ->setUser($orderUser)
            ;
        } catch (\PagaMasTarde\OrdersApiClient\Exception\ValidationException $validationException) {
            PrestaShopLogger::addLog(
                'PagaMasTarde Order Validation Exception For user ' .
                $customer->email .
                ' : ' .
                $validationException->getMessage(),
                3,
                $validationException->getCode(),
                null,
                null,
                true
            );
            Tools::redirect($cancelUrl);
        }

        try {
            $orderClient = new PagaMasTarde\OrdersApiClient\Client(
                $paylaterPublicKey,
                $paylaterPrivateKey
            );
            $order = $orderClient->createOrder($order);
            if ($order instanceof \PagaMasTarde\OrdersApiClient\Model\Order) {
                $url = $order->getActionUrls()->getForm();
                $result = Db::getInstance()->insert(
                    'pmt_order',
                    array(
                        'id' => $cart->id,
                        'order_id' => $order->getId()
                    ),
                    false,
                    false,
                    Db::ON_DUPLICATE_KEY,
                    true
                );
                if (!$result) {
                    throw new \Exception('Unable to save pmt-order-id');
                }
            } else {
                throw new \Exception('Order not created');
            }
        } catch (\Exception $exception) {
            PrestaShopLogger::addLog(
                'PagaMasTarde OrderException For user ' .
                $customer->email .
                ' : ' .
                $exception->getMessage(),
                3,
                $exception->getCode(),
                null,
                null,
                true
            );
            Tools::redirect($cancelUrl);
        }

        if (!$iframe) {
            Tools::redirect($url);
        } else {
            $this->context->smarty->assign(array(
                'url'           => $url,
                'checkoutUrl'   => $cancelUrl,
            ));

            try {
                if (_PS_VERSION_ < 1.7) {
                    $this->setTemplate('payment-15.tpl');
                } else {
                    $this->setTemplate('module:paylater/views/templates/front/payment-17.tpl');
                }
            } catch (\Exception $exception) {
                Tools::redirect($url);
            }
        }
    }
}
