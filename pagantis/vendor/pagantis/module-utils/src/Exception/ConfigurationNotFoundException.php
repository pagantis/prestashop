<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class ConfigurationNotFoundException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class ConfigurationNotFoundException extends AbstractException
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
     * ConfigurationNotFoundException constructor.
     */
    public function __construct()
    {
        $this->code = self::ERROR_CODE;
        $this->message = self::ERROR_MESSAGE;

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
