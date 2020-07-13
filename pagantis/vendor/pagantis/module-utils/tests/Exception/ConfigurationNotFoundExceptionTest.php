<?php

namespace Tests\Pagantis\ModuleUtils;

use Pagantis\ModuleUtils\Exception\ConfigurationNotFoundException;

/**
 * Class ConfigurationNotFoundException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class ConfigurationNotFoundExceptionTest extends AbstractExceptionTest
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Unable to load module configuration';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 400;

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $exception = new ConfigurationNotFoundException();
        $this->assertEquals(self::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals(self::ERROR_CODE, $exception->getCode());
    }

    /**
     * testConstant
     */
    public function testConstant()
    {
        $this->assertEquals(self::ERROR_MESSAGE, ConfigurationNotFoundException::ERROR_MESSAGE);
        $this->assertEquals(self::ERROR_CODE, ConfigurationNotFoundException::ERROR_CODE);
    }
}
