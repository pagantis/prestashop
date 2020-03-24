<?php
/**
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
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
    /** @var string $language */
    protected $language;

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
        $iframe = Pagantis::getExtraConfig('PAGANTIS_FORM_DISPLAY_TYPE');
        $cancelUrl = (Pagantis::getExtraConfig('PAGANTIS_URL_KO') !== '') ?
            Pagantis::getExtraConfig('PAGANTIS_URL_KO') : $koUrl;
        $pagantisPublicKey = Configuration::get('pagantis_public_key');
        $pagantisPrivateKey = Configuration::get('pagantis_private_key');
        $okUrl = _PS_BASE_URL_SSL_.__PS_BASE_URI__
            .'index.php?canonical=true&fc=module&module=pagantis&controller=notify&origin=redirect&'
            .http_build_query($query)
        ;
        $notificationOkUrl = _PS_BASE_URL_SSL_.__PS_BASE_URI__
            .'index.php?canonical=true&fc=module&module=pagantis&controller=notify&origin=notification&'
            .http_build_query($query)
        ;

        $shippingAddress = new Address($cart->id_address_delivery);
        $billingAddress = new Address($cart->id_address_invoice);
        $curlInfo = curl_version();
        $curlVersion = $curlInfo['version'];
        $metadata = array(
            'pg_module' => 'prestashop',
            'pg_version' => $this->module->version,
            'ec_module' => 'prestashop',
            'ec_version' => _PS_VERSION_
        );

        try {
            $shippingCountry = Country::getIsoById($shippingAddress->id_country);
            $userAddress =  new \Pagantis\OrdersApiClient\Model\Order\User\Address();
            $userAddress
                ->setZipCode($shippingAddress->postcode)
                ->setFullName($shippingAddress->firstname . ' ' . $shippingAddress->lastname)
                ->setCountryCode($shippingCountry)
                ->setCity($shippingAddress->city)
                ->setAddress($shippingAddress->address1 . ' ' . $shippingAddress->address2)
                ->setTaxId($this->getTaxId($customer, $shippingAddress, $billingAddress))
                ->setNationalId($this->getNationalId($customer, $shippingAddress, $billingAddress))
            ;

            $orderShippingAddress =  new \Pagantis\OrdersApiClient\Model\Order\User\Address();
            $orderShippingAddress
                ->setZipCode($shippingAddress->postcode)
                ->setFullName($shippingAddress->firstname . ' ' . $shippingAddress->lastname)
                ->setCountryCode($shippingCountry)
                ->setCity($shippingAddress->city)
                ->setAddress($shippingAddress->address1 . ' ' . $shippingAddress->address2)
                ->setTaxId($this->getTaxId($customer, $shippingAddress, $billingAddress))
                ->setNationalId($this->getNationalId($customer, $shippingAddress, $billingAddress))
                ->setFixPhone($shippingAddress->phone)
                ->setMobilePhone($shippingAddress->phone_mobile)
            ;

            $billingCountry = Country::getIsoById($billingAddress->id_country);
            $orderBillingAddress = new \Pagantis\OrdersApiClient\Model\Order\User\Address();
            $orderBillingAddress
                ->setZipCode($billingAddress->postcode)
                ->setFullName($billingAddress->firstname . ' ' . $billingAddress->lastname)
                ->setCountryCode($billingCountry)
                ->setCity($billingAddress->city)
                ->setAddress($billingAddress->address1 . ' ' . $billingAddress->address2)
                ->setTaxId($this->getTaxId($customer, $billingAddress, $shippingAddress))
                ->setNationalId($this->getNationalId($customer, $billingAddress, $shippingAddress))
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
                ->setTaxId($this->getTaxId($customer, $shippingAddress, $billingAddress))
                ->setNationalId($this->getNationalId($customer, $shippingAddress, $billingAddress))
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
                        ->setAmount((string) floor(100 * $order['total_paid']))
                        ->setDate(new \DateTime($order['date_add']))
                    ;
                    $orderUser->addOrderHistory($orderHistory);
                }
            }

            $metadataOrder = new \Pagantis\OrdersApiClient\Model\Order\Metadata();
            foreach ($metadata as $key => $metadatum) {
                $metadataOrder->addMetadata($key, $metadatum);
            }

            $details = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details();
            $details->setShippingCost((string) floor(100 * $cart->getTotalShippingCost()));
            $items = $cart->getProducts();
            $promotedAmount = 0;
            foreach ($items as $key => $item) {
                $promotedProduct = $this->isPromoted($item['id_product']);
                $product = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details\Product();
                $product
                    ->setAmount((string) floor(100 * $item['price_wt']))
                    ->setQuantity($item['quantity'])
                    ->setDescription($item['name']);
                if ($promotedProduct) {
                    $promotedAmount+=$product->getAmount();
                    $productId = $item['id_product'];
                    $finalPrice = Product::getPriceStatic($productId);
                    $promotedMessage = 'Promoted Item: ' . $product->getDescription() .
                        ' Price: ' . $finalPrice .
                        ' Qty: ' . $product->getQuantity() .
                        ' Item ID: ' . $item['id_product'];
                    $metadataOrder->addMetadata('promotedProduct', $promotedMessage);
                }
                $details->addProduct($product);
            }


            $orderShoppingCart = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart();
            $totalAmount = (string) floor(100 * $cart->getOrderTotal(true));
            $orderShoppingCart
                ->setDetails($details)
                ->setOrderReference($cart->id)
                ->setTotalAmount($totalAmount)
                ->setPromotedAmount($promotedAmount)
            ;

            $orderConfigurationUrls = new \Pagantis\OrdersApiClient\Model\Order\Configuration\Urls();
            $orderConfigurationUrls
                ->setCancel($cancelUrl)
                ->setKo($cancelUrl)
                ->setAuthorizedNotificationCallback($notificationOkUrl)
                ->setRejectedNotificationCallback(null)
                ->setOk($okUrl)
            ;

            $orderChannel = new \Pagantis\OrdersApiClient\Model\Order\Configuration\Channel();
            $orderChannel
                ->setAssistedSale(false)
                ->setType(\Pagantis\OrdersApiClient\Model\Order\Configuration\Channel::ONLINE)
            ;

            $purchaseCountry = $this->getUserLanguage($shippingAddress, $billingAddress);
            $orderConfiguration = new \Pagantis\OrdersApiClient\Model\Order\Configuration();
            $orderConfiguration
                ->setChannel($orderChannel)
                ->setUrls($orderConfigurationUrls)
                ->setPurchaseCountry($purchaseCountry)
            ;

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
                /** @var string $orderId MD5 value */
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

    /**
     * @param null $customer
     * @param null $addressOne
     * @param null $addressTwo
     * @return mixed|null
     */
    private function getNationalId($customer = null, $addressOne = null, $addressTwo = null)
    {
        if ($customer !== null && isset($customer->national_id)) {
            return $customer->national_id;
        } elseif ($addressOne !== null and isset($addressOne->national_id)) {
            return $addressOne->national_id;
        } elseif ($addressOne !== null and isset($addressOne->dni)) {
            return $addressOne->dni;
        } elseif ($addressTwo !== null and isset($addressTwo->national_id)) {
            return $addressTwo->national_id;
        } elseif ($addressTwo !== null and isset($addressTwo->dni)) {
            return $addressTwo->dni;
        } else {
            return null;
        }
    }

    /**
     * @param null $customer
     * @param null $addressOne
     * @param null $addressTwo
     * @return mixed|null
     */
    private function getTaxId($customer = null, $addressOne = null, $addressTwo = null)
    {
        if ($customer !== null && isset($customer->tax_id)) {
            return $customer->tax_id;
        } elseif ($customer !== null && isset($customer->fiscalcode)) {
            return $customer->fiscalcode;
        } elseif ($addressOne !== null and isset($addressOne->tax_id)) {
            return $addressOne->tax_id;
        } elseif ($addressTwo !== null and isset($addressTwo->tax_id)) {
            return $addressTwo->tax_id;
        } else {
            return null;
        }
    }

    /**
     * @param $item
     *
     * @return bool
     */
    private function isPromoted($itemId)
    {
        $itemCategories = ProductCore::getProductCategoriesFull($itemId);
        if (in_array(PROMOTIONS_CATEGORY_NAME, $this->arrayColumn($itemCategories, 'name')) !== false) {
            return true;
        }
        return false;
    }

    /**
     * @param array $input
     * @param       $columnKey
     * @param null  $indexKey
     *
     * @return array|bool
     */
    private function arrayColumn(array $input, $columnKey, $indexKey = null)
    {
        $array = array();
        foreach ($input as $value) {
            if (!array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }

    /**
     * @param null $shippingAddress
     * @param null $billingAddress
     * @return string
     */
    private function getUserLanguage($shippingAddress = null, $billingAddress = null)
    {
        $allowedCountries    = unserialize(Pagantis::getExtraConfig('PAGANTIS_ALLOWED_COUNTRIES'));
        $lang = Language::getLanguage($this->context->language->id);
        $langArray = explode("-", $lang['language_code']);
        if (count($langArray) != 2 && isset($lang['locale'])) {
            $langArray = explode("-", $lang['locale']);
        }
        $language = Tools::strtoupper($langArray[count($langArray)-1]);
        // Prevent null language detection
        if (in_array(Tools::strtolower($language), $allowedCountries)) {
            return $language;
        }
        if ($shippingAddress) {
            $language = Country::getIsoById($shippingAddress->id_country);
            if (in_array(Tools::strtolower($language), $allowedCountries)) {
                return $language;
            }
        }
        if ($billingAddress) {
            $language = Country::getIsoById($billingAddress->id_country);
            if (in_array(Tools::strtolower($language), $allowedCountries)) {
                return $language;
            }
        }
        return 'ES';
    }
}
