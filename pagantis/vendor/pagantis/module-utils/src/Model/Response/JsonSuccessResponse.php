<?php

namespace Pagantis\ModuleUtils\Model\Response;

/**
 * Class JsonSuccessResponse
 * @package Pagantis\ModuleUtils\Model
 */
class JsonSuccessResponse extends AbstractJsonResponse
{
    /**
     * RESULT_DESCRIPTION
     */
    const RESULT = 'Order confirmed';

    /**
     * STATUS_CODE
     */
    const STATUS_CODE = 200;

    /**
     * @var string $result
     */
    protected $result;

    /**
     * @var int $status
     */
    protected $statusCode;

    /**
     * JsonSuccessResponse constructor.
     */
    public function __construct()
    {
        $this->result = self::RESULT;
        $this->statusCode = self::STATUS_CODE;

        parent::__construct();
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
