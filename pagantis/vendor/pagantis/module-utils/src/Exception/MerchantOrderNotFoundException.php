<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class MerchantOrderNotFoundException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class MerchantOrderNotFoundException extends AbstractException
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Merchant order not found';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 404;

    /**
     * MerchantOrderNotFoundException constructor.
     */
    public function __construct()
    {
        $this->code = self::ERROR_CODE;
        $this->message = self::ERROR_MESSAGE;

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
