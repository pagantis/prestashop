# Module Utils <img src="https://www.pagantis.com/wp-content/uploads/2019/02/cropped-pagantis_logo-1.png" width="100" align="right">

CircleCI: [![CircleCI](https://circleci.com/gh/pagantis/orders-api-client/tree/master.svg?style=svg)](https://circleci.com/gh/pagantis/orders-api-client/tree/master)

[![Latest Stable Version](https://poser.pugx.org/pagantis/module-utils/v/stable)](https://packagist.org/packages/pagantis/module-utils)
[![composer.lock](https://poser.pugx.org/pagantis/module-utils/composerlock)](https://packagist.org/packages/pagantis/module-utils)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pagantis/module-utils/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pagantis/module-utils/?branch=master)

Module Utils will help you when developing integration test of Pagantis. The library will use the Exception object to
find some problem. Check each exception type to find the error which causes the problem. 

## How to use

Install the library by:

- Downloading it from [here](https://github.com/pagantis/module-utils/releases/latest)

- Using Composer:
```php
composer require pagantis/module-utils
```
Finally, be sure to include the autoloader:
```php
require_once '/path/to/your-project/vendor/autoload.php';
```

Once the library is ready and inside the project the stub objects will available and the moduleUtils will also 
available.

```php
//Amount mismatch Example
try {
    if ($price1 != $price2) {
        throw new AmountMismatchException($price1, $price2);
    }
} catch(AmountMismatchException $e) {
    $jsonResponse = new JsonExceptionResponse();
    $jsonResponse->setException($e);
    $jsonResponse->toJson();
}

```

## Help us to improve

We are happy to accept suggestions or pull requests. If you are willing to help us develop better software
please create a pull request here following the PSR-2 code style and we will use reviewable to check
the code and if al test pass and no issues are detected by SensioLab Insights you could will be ready
to merge.

* [Issue Tracker](https://github.com/pagantis/module-utils/issues)
