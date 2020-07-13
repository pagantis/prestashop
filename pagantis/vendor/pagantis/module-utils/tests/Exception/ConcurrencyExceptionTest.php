<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\ConcurrencyException;

/**
 * Class ConcurrencyException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class ConcurrencyExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Validation in progress, try again later';
    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 429;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new ConcurrencyException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, ConcurrencyException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, ConcurrencyException::ERROR_CODE);
    }
}
