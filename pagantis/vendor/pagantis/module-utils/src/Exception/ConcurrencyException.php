<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class ConcurrencyException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class ConcurrencyException extends AbstractException
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
     * ConcurrencyException constructor.
     */
    public function __construct()
    {
        $this->code = self::ERROR_CODE;
        $this->message = self::ERROR_MESSAGE;

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
