<?php

namespace Pagantis\ModuleUtils\Model\Response;

/**
 * Class JsonExceptionResponse
 * @package Pagantis\ModuleUtils\Model
 */
class JsonExceptionResponse extends AbstractJsonResponse
{
    /**
     * RESULT_DESCRIPTION
     */
    const RESULT = 'Order not confirmed';

    /**
     * STATUS_CODE
     */
    const STATUS_CODE = 500;

    /**
     * @var string $result
     */
    protected $result;

    /**
     * @var int $status
     */
    protected $statusCode;

    /**
     * JsonExceptionResponse constructor.
     */
    public function __construct()
    {
        $this->result = self::RESULT;
        $this->statusCode = self::STATUS_CODE;

        parent::__construct();
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception)
    {
        $this->result = $exception->getMessage();
        $this->statusCode = $exception->getCode();
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }
}
