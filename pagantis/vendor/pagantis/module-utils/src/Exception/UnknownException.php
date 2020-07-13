<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class UnknownException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class UnknownException extends AbstractException
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Unknown Exception: %s';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 500;

    /**
     * UnknownException constructor.
     *
     * @param $message
     */
    public function __construct($message)
    {
        $this->code = self::ERROR_CODE;
        $this->message = sprintf(self::ERROR_MESSAGE, $message);

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
