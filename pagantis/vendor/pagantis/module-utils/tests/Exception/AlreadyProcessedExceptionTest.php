<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\AlreadyProcessedException;

/**
 * Class AlreadyProcessedException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class AlreadyProcessedExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Cart already processed';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 200;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new AlreadyProcessedException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, AlreadyProcessedException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, AlreadyProcessedException::ERROR_CODE);
    }
}
