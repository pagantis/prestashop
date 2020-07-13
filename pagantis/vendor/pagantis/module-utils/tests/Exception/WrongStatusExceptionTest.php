<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\WrongStatusException;

/**
 * Class WrongStatusException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class WrongStatusExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Order status is not authorized. Current status: %s';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 403;

    /**
     * ERROR_STATUS
     */
    const ERROR_STATUS = 'UNCONFIRMED';

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new WrongStatusException(self::ERROR_STATUS);
        $message = sprintf(self::ERROR_MESSAGE, self::ERROR_STATUS);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, WrongStatusException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, WrongStatusException::ERROR_CODE);
    }
}
