<?php

namespace Tests\Pagantis\ModuleUtils\Model\Response;

use Pagantis\ModuleUtils\Model\Response\JsonSuccessResponse;
use PHPUnit\Framework\TestCase;

class JsonSuccessResponseTest extends TestCase
{
    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $jsonSuccessResponse = new JsonSuccessResponse();

        $this->assertEquals($jsonSuccessResponse->getResult(), JsonSuccessResponse::RESULT);
        $this->assertEquals($jsonSuccessResponse->getStatusCode(), JsonSuccessResponse::STATUS_CODE);
    }
}
