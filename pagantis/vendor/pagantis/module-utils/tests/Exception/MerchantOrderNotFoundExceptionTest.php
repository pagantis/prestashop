<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\MerchantOrderNotFoundException;

/**
 * Class MerchantOrderNotFoundException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class MerchantOrderNotFoundExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Merchant order not found';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 404;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new MerchantOrderNotFoundException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, MerchantOrderNotFoundException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, MerchantOrderNotFoundException::ERROR_CODE);
    }
}
