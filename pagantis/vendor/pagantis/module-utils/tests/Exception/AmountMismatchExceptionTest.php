<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\AmountMismatchException;

/**
 * Class AmountMismatchException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class AmountMismatchExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Amount mismatch error, expected %s and received %s';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 409;

    /**
     * EXPECTED_AMOUNT
     */
    const EXPECTED_AMOUNT = 10;

    /**
     * CURRENT_AMOUNT
     */
    const CURRENT_AMOUNT = 15;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new AmountMismatchException(
            self::EXPECTED_AMOUNT,
            self::CURRENT_AMOUNT
        );
        $message = sprintf(self::ERROR_MESSAGE, self::EXPECTED_AMOUNT, self::CURRENT_AMOUNT);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, AmountMismatchException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, AmountMismatchException::ERROR_CODE);
    }
}
