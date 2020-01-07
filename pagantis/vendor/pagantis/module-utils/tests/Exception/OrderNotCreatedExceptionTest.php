<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\OrderNotCreatedException;

/**
 * Class OrderNotCreatedException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class OrderNotCreatedExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Unable to create order with the data provided';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 400;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new OrderNotCreatedException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, OrderNotCreatedException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, OrderNotCreatedException::ERROR_CODE);
    }
}
