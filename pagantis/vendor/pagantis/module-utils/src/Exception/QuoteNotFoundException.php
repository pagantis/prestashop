<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class QuoteNotFoundException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class QuoteNotFoundException extends AbstractException
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
     * QuoteNotFoundException constructor.
     */
    public function __construct()
    {
        $this->code = self::ERROR_CODE;
        $this->message = self::ERROR_MESSAGE;

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
