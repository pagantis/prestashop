<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\QuoteNotFoundException;

/**
 * Class QuoteNotFoundException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class QuoteNotFoundExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'No quote found';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 429;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new QuoteNotFoundException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, QuoteNotFoundException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, QuoteNotFoundException::ERROR_CODE);
    }
}
