<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class NoIdentificationException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class NoIdentificationException extends AbstractException
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
     * NoIdentificationException constructor.
     */
    public function __construct()
    {
        $this->code = self::ERROR_CODE;
        $this->message = self::ERROR_MESSAGE;

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
