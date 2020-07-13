<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\OrderNotFoundException;

/**
 * Class OrderNotFoundException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class OrderNotFoundExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Unable to get the order in Pagantis';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 400;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new OrderNotFoundException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, OrderNotFoundException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, OrderNotFoundException::ERROR_CODE);
    }
}
