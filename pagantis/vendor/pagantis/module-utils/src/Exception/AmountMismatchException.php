<?php

namespace Pagantis\ModuleUtils\Exception;

/**
 * Class AmountMismatchException
 *
 * @package Pagantis\ModuleUtils\Exception
 */
class AmountMismatchException extends AbstractException
{
    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Amount mismatch error, expected %s and received %s';

    /**
     * ERROR_CODE
     */
    const ERROR_CODE = 409;

    /**
     * AmountMismatchException constructor.
     *
     * @param $expectedAmount
     * @param $currentAmount
     */
    public function __construct($expectedAmount, $currentAmount)
    {
        $this->code = self::ERROR_CODE;
        $this->message = sprintf(self::ERROR_MESSAGE, $expectedAmount, $currentAmount);

        return parent::__construct($this->getMessage(), $this->getCode());
    }
}
