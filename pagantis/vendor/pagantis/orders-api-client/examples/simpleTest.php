<?php

//Require the Client library using composer: composer require pagantis/orders-api-client
require_once('../vendor/autoload.php');

/**
 * PLEASE FILL YOUR PUBLIC KEY AND PRIVATE KEY
 */
const PUBLIC_KEY = ''; //Set your public key
const PRIVATE_KEY = ''; //Set your private key
const ORDER_ID = 'order_4159972708';

try {
    session_start();
    $method = ($_GET['action']) ? ($_GET['action']) : 'createOrder';
    call_user_func($method);
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

/**
 * Create order in Pagantis
 *
 * @throws \Httpful\Exception\ConnectionErrorException
 * @throws \Pagantis\OrdersApiClient\Exception\ClientException
 * @throws \Pagantis\OrdersApiClient\Exception\HttpException
 * @throws \Exception
 */
function createOrder()
{
    // There are 3 objects which are mandatory: User object, ShoppingCart object and Configuration object.
    //1. User Object
    writeLog('Creating User object');
    writeLog('Adding the address of the user');
    $userAddress =  new \Pagantis\OrdersApiClient\Model\Order\User\Address();
    $userAddress
        ->setZipCode('28031')
        ->setFullName('María Sanchez Escudero')
        ->setCountryCode('ES')
        ->setCity('Madrid')
        ->setAddress('Paseo de la Castellana, 95')
        ->setNationalId('59661738Z')
        ->setFixPhone('911231234')
        ->setMobilePhone('600123123');

    $orderBillingAddress = $userAddress;

    $orderShippingAddress =  new \Pagantis\OrdersApiClient\Model\Order\User\Address();
    $orderShippingAddress
        ->setZipCode('08029')
        ->setFullName('Alberto Escudero Sanchez')
        ->setCountryCode('ES')
        ->setCity('Barcelona')
        ->setAddress('Avenida de la diagonal 525')
        ->setNationalId('59661738Z')
        ->setFixPhone('931232345')
        ->setMobilePhone('600123124');

    writeLog('Adding the information of the user');
    $orderUser = new \Pagantis\OrdersApiClient\Model\Order\User();
    $orderUser
        ->setFullName('María Sanchez Escudero')
        ->setAddress($userAddress)
        ->setBillingAddress($orderBillingAddress)
        ->setShippingAddress($orderShippingAddress)
        ->setDateOfBirth('1985-12-30')
        ->setEmail('user@my-shop.com')
        ->setFixPhone('911231234')
        ->setMobilePhone('600123123')
        ->setNationalId('59661738Z');
    writeLog('Created User object');

    //2. ShoppingCart Object
    writeLog('Creating ShoppingCart object');
    writeLog('Adding the purchases of the customer, if there are.');
    $orderHistory = new \Pagantis\OrdersApiClient\Model\Order\User\OrderHistory();
    $orderHistory
        ->setAmount('2499')
        ->setDate('2010-01-31');
    $orderUser->addOrderHistory($orderHistory);

    writeLog('Adding cart products. Minimum 1 required');
    $product = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details\Product();
    $product
        ->setAmount('59999')
        ->setQuantity('1')
        ->setDescription('TV LG UltraPlana');

    $details = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details();
    $details->setShippingCost('0');
    $details->addProduct($product);

    $orderShoppingCart = new \Pagantis\OrdersApiClient\Model\Order\ShoppingCart();
    $orderShoppingCart
        ->setDetails($details)
        ->setOrderReference(ORDER_ID)
        ->setPromotedAmount(0) // This amount means that the merchant will asume the interests.
        ->setTotalAmount('59999');
    writeLog('Created OrderShoppingCart object');

    //3. Configuration Object
    writeLog('Creating Configuration object');
    writeLog('Adding urls to redirect the user according each case');
    $confirmUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?action=confirmOrder";
    $errorUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?action=cancelOrder";
    $orderConfigurationUrls = new \Pagantis\OrdersApiClient\Model\Order\Configuration\Urls();
    $orderConfigurationUrls
        ->setCancel($errorUrl)
        ->setKo($errorUrl)
        ->setAuthorizedNotificationCallback($confirmUrl)
        ->setRejectedNotificationCallback($confirmUrl)
        ->setOk($confirmUrl);

    writeLog('Adding channel info');
    $orderChannel = new \Pagantis\OrdersApiClient\Model\Order\Configuration\Channel();
    $orderChannel
        ->setAssistedSale(false)
        ->setType(\Pagantis\OrdersApiClient\Model\Order\Configuration\Channel::ONLINE);

    $orderConfiguration = new \Pagantis\OrdersApiClient\Model\Order\Configuration();
    $orderConfiguration
        ->setChannel($orderChannel)
        ->setUrls($orderConfigurationUrls);
    writeLog('Created Configuration object');

    $order = new \Pagantis\OrdersApiClient\Model\Order();
    $order
        ->setConfiguration($orderConfiguration)
        ->setShoppingCart($orderShoppingCart)
        ->setUser($orderUser);

    writeLog('Creating OrdersApiClient');
    if (PUBLIC_KEY=='' || PRIVATE_KEY == '') {
        throw new \Exception('You need set the public and private key');
    }
    $orderClient = new \Pagantis\OrdersApiClient\Client(PUBLIC_KEY, PRIVATE_KEY);

    writeLog('Creating Pagantis order');
    $order = $orderClient->createOrder($order);
    if ($order instanceof \Pagantis\OrdersApiClient\Model\Order) {
        //If the order is correct and created then we have the redirection URL here:
        $url = $order->getActionUrls()->getForm();
        $_SESSION['order_id'] = $order->getId();
        writeLog(json_encode(
            $order->export(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ));
    } else {
        throw new \Exception('Order not created');
    }

    // You can use our test credit cards to fill the Pagantis form
    writeLog("Redirecting to Pagantis form => $url");
    header('Location:'. $url);
}

/**
 * Confirm order in Pagantis
 *
 * @throws \Httpful\Exception\ConnectionErrorException
 * @throws \Pagantis\OrdersApiClient\Exception\ClientException
 * @throws \Pagantis\OrdersApiClient\Exception\HttpException
 */
function confirmOrder()
{
    /* Once the user comes back to the OK url or there is a notification upon callback url you will have to confirm
     * the reception of the order. If not it will expire and will never be paid.
     *
     * Add this parameters in your database when you create a order and map it to your own order. Or search orders by
     * your own order id. Both options are possible.
     */

    writeLog('Creating OrdersApiClient');
    $orderClient = new \Pagantis\OrdersApiClient\Client(PUBLIC_KEY, PRIVATE_KEY);

    $order = $orderClient->getOrder($_SESSION['order_id']);

    if ($order instanceof \Pagantis\OrdersApiClient\Model\Order &&
        $order->getStatus() == \Pagantis\OrdersApiClient\Model\Order::STATUS_AUTHORIZED) {
        //If the order exists, and the status is authorized, means you can mark the order as paid.

        //DO WHATEVER YOU NEED TO DO TO MARK THE ORDER AS PAID IN YOUR OWN SYSTEM.
        writeLog('Confirming order');
        $order = $orderClient->confirmOrder($order->getId());

        writeLog('Order confirmed');
        writeLog(json_encode(
            $order->export(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ));
        $message = "The order {$_SESSION['order_id']} has been confirmed successfully";
    } else {
        $message = "The order {$_SESSION['order_id']} can't be confirmed";
    }

    /* The order has been marked as paid and confirmed in Pagantis so you will send the product to your customer and
     * Pagantis will pay you in the next 24h.
     */

    echo $message;
    exit;
}

/**
 * Action after redirect to cancelUrl
 */
function cancelOrder()
{
    $message = "The order {$_SESSION['order_id']} can't be created";

    echo $message;
    exit;
}

/**
 * UTILS
 */

/**
 * Write log file
 *
 * @param $message
 */
function writeLog($message)
{
    file_put_contents('pagantis.log', "$message.\n", FILE_APPEND);
}
