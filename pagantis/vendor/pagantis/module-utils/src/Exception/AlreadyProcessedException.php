<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class AlreadyProcessedException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class AlreadyProcessedException extends AbstractException
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
     * AlreadyProcessedException constructor.
     */
    public function __construct()
    {
        $this->code = self::ERROR_CODE;
        $this->message = self::ERROR_MESSAGE;

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
