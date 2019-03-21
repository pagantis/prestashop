<?php
/**
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integration@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 */

require_once('AbstractController.php');

use Pagantis\ModuleUtils\Exception\OrderNotFoundException;
use Pagantis\ModuleUtils\Exception\UnknownException;

/**
 * Class PagantisRedirectModuleFrontController
 */
class PagantisPaymentModuleFrontController extends AbstractController
{
    /**
     * @param $customer
     * @param $exception
     */
    protected function addLog($customer, $exception)
    {
        if (_PS_VERSION_ < 1.6) {
            Logger::addLog(
                'Pagantis Exception For user ' .
                $customer->email .
                ' : ' .
                $exception->getMessage(),
                3,
                $exception->getCode(),
                null,
                null,
                true
            );
        } else {
            PrestaShopLogger::addLog(
                'Pagantis Exception For user ' .
                $customer->email .
                ' : ' .
                $exception->getMessage(),
                3,
                $exception->getCode(),
                null,
                null,
                true
            );
        }
    }

    /**
     * Process Post Request
     *
     * @throws \Exception
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
        $query = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );

        $koUrl = $this->context->link->getPageLink(
            'order',
            null,
            null,
            array('step'=>3)
        );
        $iframe = getenv('PAGANTIS_FORM_DISPLAY_TYPE');
        $cancelUrl = (getenv('PAGANTIS_URL_KO') !== '') ? getenv('PAGANTIS_URL_KO') : $koUrl;
        $pagantisPublicKey = Configuration::get('pagantis_public_key');
        $pagantisPrivateKey = Configuration::get('pagantis_private_key');
        $okUrl = _PS_BASE_URL_SSL_.__PS_BASE_URI__
                 .'index.php?canonical=true&fc=module&module=pagantis&controller=notify&'
                 .http_build_query($query)
        ;

        $shippingAddress = new Address($cart->id_address_delivery);
        $billingAddress = new Address($cart->id_address_invoice);
        $curlInfo = curl_version();
        $curlVersion = $curlInfo['version'];
        $metadata = array(
            'ps' => _PS_VERSION_,
            'pagantis' => $this->module->version,
            'php' => phpversion(),
            'curl' => $curlVersion,
        );

        try {
            $userAddress =  new \Pagantis\OrdersApiClient\Model\Order\User\Address();
            $userAddress
                ->setZipCode($shippingAddress->postcode)
                ->setFullName($shippingAddress->firstname . ' ' . $shippingAddress->lastname)
                ->setCountryCode('ES')
                ->setCity($shippingAddress->city)
                ->setAddress($shippingAddress->address1 . ' ' . $shippingAddress->address2)
            ;

            $orderShippingAddress =  new \Pagantis\OrdersApiClient\Model\Order\User\Address();
            $orderShippingAddress
                ->setZipCode($shippingAddress->postcode)
                ->setFullName($shippingAddress->firstname . ' ' . $shippingAddress->lastname)
                ->setCountryCode('ES')
                ->setCity($shippingAddress->city)
                ->setAddress($shippingAddress->address1 . ' ' . $shippingAddress->address2)
                ->setDni($shippingAddress->dni)
                ->setFixPhone($shippingAddress->phone)
                ->setMobilePhone($shippingAddress->phone_mobile)
            ;

            $orderBillingAddress = new \Pagantis\OrdersApiClient\Model\Order\User\Address();
            $orderBillingAddress
                ->setZipCode($billingAddress->postcode)
                ->setFullName($billingAddress->firstname . ' ' . $billingAddress->lastname)
                ->setCountryCode('ES')
                ->setCity($billingAddress->city)
                ->setAddress($billingAddress->address1 . ' ' . $billingAddress->address2)
                ->setDni($billingAddress->dni)
                ->setFixPhone($billingAddress->phone)
                ->setMobilePhone($billingAddress->phone_mobile)
            ;

            $orderUser = new \Pagantis\OrdersApiClient\Model\Order\User();
            $orderUser
                ->setAddress($userAddress)
                ->setFullName($orderShippingAddress->getFullName())
                ->setBillingAddress($orderBillingAddress)
                ->setEmail($this->context->cookie->logged ? $this->context->cookie->email : $customer->email)
                ->setFixPhone($shippingAddress->phone)
                ->setMobilePhone($shippingAddress->phone_mobile)
                ->setShippingAddress($orderShippingAddress)
                ->setDni($shippingAddress->dni)
            ;

            if ($customer->birthday!='0000-00-00') {
                $orderUser->setDateOfBirth($customer->birthday);
            }

            $orders = Order::getCustomerOrders($customer->id);
            /** @var \PrestaShop\PrestaShop\Adapter\Entity\Order $order */
            foreach ($orders as $order) {
                if ($order['valid']) {
                    $orderHistory = new \Pagantis\OrdersApiClient\Model\Order\User\OrderHistory();
                    $orderHistory
                        ->setAmount((int) (100 * $order['total_paid']))
                        ->setDate(new \DateTime($order['date_add']))
                    ;
                    $orderUser->addOrderHistory($orderHistory);
                }
            }

            $details = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details();
            $details->setShippingCost((int) (100 * $cart->getTotalShippingCost()));
            $items = $cart->getProducts();
            foreach ($items as $key => $item) {
                $product = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details\Product();
                $product
                    ->setAmount((int) (100 * $item['price_wt']))
                    ->setQuantity($item['quantity'])
                    ->setDescription($item['name']);
                $details->addProduct($product);
            }

            $orderShoppingCart = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart();
            $orderShoppingCart
                ->setDetails($details)
                ->setOrderReference($cart->id)
                ->setPromotedAmount(0)
                ->setTotalAmount((int) (100 * $cart->getOrderTotal(true)))
            ;

            $orderConfigurationUrls = new \Pagantis\OrdersApiClient\Model\Order\Configuration\Urls();
            $orderConfigurationUrls
                ->setCancel($cancelUrl)
                ->setKo($cancelUrl)
                ->setAuthorizedNotificationCallback($okUrl)
                ->setRejectedNotificationCallback($okUrl)
                ->setOk($okUrl)
            ;

            $orderChannel = new \Pagantis\OrdersApiClient\Model\Order\Configuration\Channel();
            $orderChannel
                ->setAssistedSale(false)
                ->setType(\Pagantis\OrdersApiClient\Model\Order\Configuration\Channel::ONLINE)
            ;

            $orderConfiguration = new \Pagantis\OrdersApiClient\Model\Order\Configuration();
            $orderConfiguration
                ->setChannel($orderChannel)
                ->setUrls($orderConfigurationUrls)
            ;

            $metadataOrder = new \Pagantis\OrdersApiClient\Model\Order\Metadata();
            foreach ($metadata as $key => $metadatum) {
                $metadataOrder
                    ->addMetadata($key, $metadatum);
            }

            $order = new \Pagantis\OrdersApiClient\Model\Order();
            $order
                ->setConfiguration($orderConfiguration)
                ->setMetadata($metadataOrder)
                ->setShoppingCart($orderShoppingCart)
                ->setUser($orderUser)
            ;
        } catch (\Exception $exception) {
            $this->saveLog(array(), $exception);
            Tools::redirect($cancelUrl);
        }

        $url ='';
        try {
            $orderClient = new \Pagantis\OrdersApiClient\Client(
                $pagantisPublicKey,
                $pagantisPrivateKey
            );
            $order = $orderClient->createOrder($order);
            if ($order instanceof \Pagantis\OrdersApiClient\Model\Order) {
                $url = $order->getActionUrls()->getForm();
                $orderId = $order->getId();
                $sql = "INSERT INTO `" . _DB_PREFIX_ . "pagantis_order` (`id`, `order_id`)
                     VALUES ('$cart->id','$orderId') 
                     ON DUPLICATE KEY UPDATE `order_id` = '$orderId'";
                $result = Db::getInstance()->execute($sql);
                if (!$result) {
                    throw new UnknownException('Unable to save pagantis-order-id in database: '. $sql);
                }
            } else {
                throw new OrderNotFoundException();
            }
        } catch (\Exception $exception) {
            $this->saveLog(array(), $exception);
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
                    $this->setTemplate('module:pagantis/views/templates/front/payment-17.tpl');
                }
            } catch (\Exception $exception) {
                $this->saveLog(array(), $exception);
                Tools::redirect($url);
            }
        }
    }
}
