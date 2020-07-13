<?php

namespace Pagantis\OrdersApiClient\Exception;

/**
 * Class ClientException
 * @package Pagantis\OrdersApiClient\Exception
 */
class ClientException extends \Exception
{
    /**
     * Default Message
     */
    const MESSAGE = 'Client Error';

    /**
     * Default Code
     */
    const CODE = 0;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $code;

    /**
     * ValidationException constructor.
     *
     * @param string          $message
     * @param int             $code
     */
    public function __construct($message = "", $code = 0)
    {
        $this->message = empty($message) ? self::MESSAGE : $message;
        $this->code = empty($code) ? self::CODE : $code;

        return parent::__construct();
    }
}
