<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\NoIdentificationException;

/**
 * Class NoIdentificationException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class NoIdentificationExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'We can not get the Pagantis identification in database';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 404;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new NoIdentificationException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, NoIdentificationException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, NoIdentificationException::ERROR_CODE);
    }
}
