# Orders Api Client <img src="https://www.pagantis.com/wp-content/uploads/2019/02/cropped-pagantis_logo-1.png" width="100" align="right">

CircleCI: [![CircleCI](https://circleci.com/gh/pagantis/orders-api-client/tree/master.svg?style=svg)](https://circleci.com/gh/pagantis/orders-api-client/tree/master)

[![Latest Stable Version](https://poser.pugx.org/pagantis/orders-api-client/v/stable)](https://packagist.org/packages/pagantis/orders-api-client)
[![composer.lock](https://poser.pugx.org/pagantis/orders-api-client/composerlock)](https://packagist.org/packages/pagantis/orders-api-client)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pagantis/orders-api-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pagantis/orders-api-client/?branch=master)

Orders API Client offers the merchants working with Pagantis a way to consume the API services without the effort of doing a complete development.
The library provides stubs for each type of object withing the API and the method calls. Each Method supported by the API is implemented in this client and
is documented within the code and [here](https://developer.pagantis.com/api/)

All the code is tested and inspected by external services.

## How to use

Install the library by:

- Downloading it from [here](https://github.com/pagantis/orders-api-client/releases/latest)

https://github.com/pagantis/orders-api-client/releases/latest

- Using Composer:
```php
composer require pagantis/orders-api-client
```
Finally, be sure to include the autoloader:
```php
require_once '/path/to/your-project/vendor/autoload.php';
```

Once the library is ready and inside the project the stub objects will available and
the ordersApiClient will also available.

```php
//Create a OrdersApiClient object, for example:
$ordersApiClient = new OrdersApiClient($publicKey, $privateKey);

//Example: get an existing Order status:
$order = $ordersApiClient->getOrder($pagantisOrderId); //$pmOrderId is the id of the order
if ($order instanceof Pagantis\OrdersApiClient\Model\Order) {
    $orderStatus = $order->getStatus();
    echo $orderStatus;
}

// You can investigate the rest of the methods. And find all the documentation of the API here:
// https://developer.pagantis.com/api/

```

## Examples / DemoTool

Find [examples in PHP](https://github.com/pagantis/orders-api-client/tree/master/examples) and a [demonstration tool](https://github.com/pagantis/orders-api-client/tree/master/examples) with the complete integration [here](https://github.com/pagantis/orders-api-client/tree/master/examples)

## Objects

The objects used inside the API are already defined as Classes with the desired properties. Each object has a setup
of setters and getters for easy validation and OOP.

Inside `src/Model` find defined the Order Object. Inside Order folder it is possible to see each element that the main
Order object has.

Use always the defined objects when using the API Client. For example when creating a refund:
```php
<?php

//Use the API Client to operate with the API
$orderApiClient = new Pagantis\OrdersApiClient\Client(
    $publicKey,
    $privateKey
);

//Create a Refund object and set the amounts:
$refund = new Pagantis\OrdersApiClient\Model\Order\Refund();
$refund
    ->setPromotedAmount(0)
    ->setTotalAmount(10)
;

//Then use the API client to generate a the refund:
$refundCreated = $apiClient->refundOrder($orderId, $refund);
?>

@Exception Handling

use Try|Catch when using the API methods, since it can cause HTTP exceptions.
```

## API Methods

### Create Order

To create a order using the API Client start from a empty Order object,
create the sub-objects and set the mandatory information.

Then send the API Call to Pagantis using the API Client. The result is the same
order object with the rest of the fields completed. The status is `CREATED`.

Store the relation between Pagantis order id and the merchant order id to be able to identify orders after creation.


```php
<?php

//Use the API Client to operate with the API
$orderApiClient = new Pagantis\OrdersApiClient\Client(
    $publicKey,
    $privateKey
);

$order = new \Pagantis\OrdersApiClient\Model\Order();
$order
    ->setConfiguration($configurationObject)
    ->setShoppingCart($shoppingCartObject)
    ->setUser($userObject)
    ->setMetadata($metadataObject)
;

//Notice, Create and fill with information the 4 objects

//To see the JSON result of a order Object:
echo json_encode(
  $order->export(),
  JSON_PRETTY_PRINT |
  JSON_UNESCAPED_SLASHES |
  JSON_UNESCAPED_UNICODE
);

//Finally create the order by using the client:
$orderCreated = $orderApiClient->createOrder($order);



/*
 * The Order object is defined inside the library and is prepared for OOP.
 * The attributes work with GETTERS and SETTERS
 *
 * The Response is parsed within the Client and is transformed into a Previously Defined Object with
 * useful methods.
 */

?>

@Exception Handling

use Try|Catch when using order Create method, since it can cause HTTP exceptions.

When setting data into the order object there is Client Exceptions that may force to set the attributes in the
correct format.
```

### Get Order

Use the method Get Order to retrieve the order again from Pagantis server. The order retrieved has updated status.
Store the relation between Pagantis order id and the merchant order id to be able to identify orders after creation.


```php
<?php

//Use the API Client to operate with the API
$orderApiClient = new Pagantis\OrdersApiClient\Client(
    $publicKey,
    $privateKey
);

//By storing the Pagantis order ID, fetch back the updated order:
$order = $orderApiClient->getOrder($orderId);

?>

@Exception Handling

use Try|Catch when using get Order method, since it can cause HTTP exceptions.

<?php

/** Tip: Navigate inside the fetched order properties:

/** $status array() **/
$status = $order->getStatus();

/** $amount int **/
$amount = $order->getShoppingCart()->getTotalAmount();

/** $createdAt \DateTime **/
$createdAt = $order->getCreateAt();

/** $refunds Refund[] **/
$refunds = $order->getRefunds();
```

### List Orders

Find the order, get all orders from yesterday, see online or instore orders, list Confirmed orders.
Use this service to find orders in the system. Use query string for result filtering.
See all the queryString parameters [here](https://developer.pagantis.com/api/)

```php
<?php

//Use the API Client to operate with the API
$orderApiClient = new Pagantis\OrdersApiClient\Client(
    $publicKey,
    $privateKey
);

//Define the queryString parameters to filter:
$queryString = [
    'channel'       => 'online',
    'pageSize'      => 2,
    'page'          => 1,
    'status'        => Order::STATUS_CONFIRMED,
    'createdFrom'   => '2018-06-28T14:08:01',
    'createdTo'     => '2018-06-28T14:08:03',
];

$orders = $orderApiClient->listOrders($queryString);

?>

@Exception Handling

use Try|Catch when using get Order method, since it can cause HTTP exceptions.



<?php

/** Tip: Iterate inside the fetched list of Orders:

// Calculate the total amount of sales withing the orders: List the orders from yesterday with status CONFIRMED and...

$amount = 0;

foreach ($orders as $order) {
    $amount += $order->getShoppingCart()->getTotalAmount();
}

// In cents, the total amount of sales from yesterday.
echo $amount;
```

### Confirm Order

When the order is AUTHORIZED confirm is the action of the merchant that informs the payment method that he validates
and confirms that the user has paid the order.
Confirmed orders are processed and the loan is created. Once a loan is confirmed it is able to have refunds.

Several callbacks can be added to the order for notification of orders authorized or rejected.
Also it is possible to list all the orders that are pending confirmation.

```php
<?php

//Use the API Client to operate with the API
$orderApiClient = new Pagantis\OrdersApiClient\Client(
    $publicKey,
    $privateKey
);

$order = $orderApiClient->confirmOrder($orderId);

/** @See https://github.com/pagantis/orders-api-client **/

?>

@Exception Handling

use Try|Catch when using get Order method, since it can cause HTTP exceptions.

<?php

/** Tip: List the AUTHORIZED orders and confirm them to prevent loosing any transaction:

$authorizedOrders = $orderApiClient->listOrders([
    'status' => Order::STATUS_AUTHORIZED,
]);

foreach ($orders as $order) {
    // validate the payment in the merchant system and then inform Pagantis API that
    // the order is processed and valid in the merchant
    $orderConfirmed = $orderApiClient->confirmOrder($order->getId());
}

?>

Remember that if a AUTHORIZED order is not confirmed, the payment will
be released and the loan will not be created. It is mandatory to
confirm all AUTHORIZED orders.
```

### Refund Order

Refund is a deduction of the order total_amount. Refund can only be requested over a confirmed order.
The refund of an order is automatically decreasing the amount from the end of the installments.

A order can have several refunds, as long as they do not reach the order total_amount.
Once the total_amount is refunded, the order status will keep to CONFIRMED.

```php
<?php

//Use the API Client to operate with the API
$orderApiClient = new Pagantis\OrdersApiClient\Client(
    $publicKey,
    $privateKey
);

//Create a Refund object and set the amounts:
$refund = new Pagantis\OrdersApiClient\Model\Order\Refund();
$refund
    ->setPromotedAmount(0)
    ->setTotalAmount(10)
;

//Then use the API client to generate a the refund:
$refundCreated = $apiClient->refundOrder($orderId, $refund);

?>

@Exception Handling

use Try|Catch when using get Order method, since it can cause HTTP exceptions.
```

## Help us to improve

We are happy to accept suggestions or pull requests. If you are willing to help us develop better software
please create a pull request here following the PSR-2 code style and we will use reviewable to check
the code and if al test pass and no issues are detected by SensioLab Insights you could will be ready
to merge. 

* [Issue Tracker](https://github.com/pagantis/orders-api-client/issues)
