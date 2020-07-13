<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\UnknownException;

/**
 * Class UnknownException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class UnknownExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Unknown Exception: %s';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 500;

    /**
     * ERROR_DESCRIPTION
     */
    const ERROR_DESCRIPTION = 'Random message';

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new UnknownException(self::ERROR_DESCRIPTION);
        $message = sprintf(self::ERROR_MESSAGE, self::ERROR_DESCRIPTION);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, UnknownException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, UnknownException::ERROR_CODE);
    }
}
