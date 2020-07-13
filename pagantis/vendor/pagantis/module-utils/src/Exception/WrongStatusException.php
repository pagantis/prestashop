<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class WrongStatusException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class WrongStatusException extends AbstractException
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Order status is not authorized. Current status: %s';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 403;

    /**
     * WrongStatusException constructor.
     *
     * @param $currentStatus
     */
    public function __construct($currentStatus)
    {
        $this->code = self::ERROR_CODE;
        $this->message = sprintf(self::ERROR_MESSAGE, $currentStatus);

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
