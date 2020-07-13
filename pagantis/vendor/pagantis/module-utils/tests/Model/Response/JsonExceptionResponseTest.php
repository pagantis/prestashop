<?php

namespace Tests\Pagantis\ModuleUtils\Model\Response;

use Pagantis\ModuleUtils\Exception\AlreadyProcessedException;
use Pagantis\ModuleUtils\Exception\AmountMismatchException;
use Pagantis\ModuleUtils\Exception\ConcurrencyException;
use Pagantis\ModuleUtils\Exception\MerchantOrderNotFoundException;
use Pagantis\ModuleUtils\Exception\NoIdentificationException;
use Pagantis\ModuleUtils\Exception\OrderNotFoundException;
use Pagantis\ModuleUtils\Exception\QuoteNotFoundException;
use Pagantis\ModuleUtils\Exception\ConfigurationNotFoundException;
use Pagantis\ModuleUtils\Exception\UnknownException;
use Pagantis\ModuleUtils\Exception\WrongStatusException;
use Pagantis\ModuleUtils\Model\Response\JsonExceptionResponse;
use Pagantis\ModuleUtils\Model\Response\JsonSuccessResponse;
use PHPUnit\Framework\TestCase;
use Tests\Pagantis\ModuleUtils\AmountMismatchExceptionTest;
use Tests\Pagantis\ModuleUtils\UnknownExceptionTest;
use Tests\Pagantis\ModuleUtils\WrongStatusExceptionTest;

class JsonExceptionResponseTest extends TestCase
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

    /**
     * testSetExceptionWithAlreadyProcessedException
     */
    public function testSetExceptionWithAlreadyProcessedException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new AlreadyProcessedException());

        $this->assertEquals($jsonExceptionResponse->getStatusCode(), AlreadyProcessedException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), AlreadyProcessedException::ERROR_MESSAGE);
    }

    /**
     * testSetExceptionWithAmountMismatchException
     */
    public function testSetExceptionWithAmountMismatchException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(
            new AmountMismatchException(
                AmountMismatchExceptionTest::EXPECTED_AMOUNT,
                AmountMismatchExceptionTest::CURRENT_AMOUNT
            )
        );

        $message = sprintf(
            AmountMismatchExceptionTest::ERROR_MESSAGE,
            AmountMismatchExceptionTest::EXPECTED_AMOUNT,
            AmountMismatchExceptionTest::CURRENT_AMOUNT
        );
        $this->assertEquals($jsonExceptionResponse->getStatusCode(), AmountMismatchExceptionTest::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), $message);
    }

    /**
     * testSetExceptionWithConcurrencyException
     */
    public function testSetExceptionWithConcurrencyException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new ConcurrencyException());

        $this->assertEquals($jsonExceptionResponse->getStatusCode(), ConcurrencyException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), ConcurrencyException::ERROR_MESSAGE);
    }

    /**
     * testSetExceptionWithMerchantOrderNotFoundException
     */
    public function testSetExceptionWithMerchantOrderNotFoundException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new MerchantOrderNotFoundException());

        $this->assertEquals($jsonExceptionResponse->getStatusCode(), MerchantOrderNotFoundException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), MerchantOrderNotFoundException::ERROR_MESSAGE);
    }

   /**
     * testSetExceptionWithMerchantConfigurationNotFoundException
     */
    public function testSetExceptionWithMerchantConfigurationNotFoundException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new ConfigurationNotFoundException());

        $this->assertEquals($jsonExceptionResponse->getStatusCode(), ConfigurationNotFoundException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), ConfigurationNotFoundException::ERROR_MESSAGE);
    }

    /**
     * testSetExceptionWithNoIdentificationException
     */
    public function testSetExceptionWithNoIdentificationException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new NoIdentificationException());

        $this->assertEquals($jsonExceptionResponse->getStatusCode(), NoIdentificationException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), NoIdentificationException::ERROR_MESSAGE);
    }

    /**
     * testSetExceptionWithOrderNotFoundException
     */
    public function testSetExceptionWithOrderNotFoundException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new OrderNotFoundException());

        $this->assertEquals($jsonExceptionResponse->getStatusCode(), OrderNotFoundException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), OrderNotFoundException::ERROR_MESSAGE);
    }

    /**
     * testSetExceptionWithNo
     *
    QuoteNotFoundException
     */
    public function testSetExceptionWithQuoteNotFoundException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new QuoteNotFoundException());

        $this->assertEquals($jsonExceptionResponse->getStatusCode(), QuoteNotFoundException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), QuoteNotFoundException::ERROR_MESSAGE);
    }

    /**
     * testSetExceptionWithUnknownException
     */
    public function testSetExceptionWithUnknownException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new UnknownException(UnknownExceptionTest::ERROR_DESCRIPTION));

        $message = sprintf(UnknownExceptionTest::ERROR_MESSAGE, UnknownExceptionTest::ERROR_DESCRIPTION);
        $this->assertEquals($jsonExceptionResponse->getStatusCode(), UnknownException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), $message);
    }

    /**
     * testSetExceptionWithWrongStatusException
     */
    public function testSetExceptionWithWrongStatusException()
    {
        $jsonExceptionResponse = new JsonExceptionResponse();
        $jsonExceptionResponse->setException(new WrongStatusException(WrongStatusExceptionTest::ERROR_STATUS));

        $message = sprintf(WrongStatusException::ERROR_MESSAGE, WrongStatusExceptionTest::ERROR_STATUS);
        $this->assertEquals($jsonExceptionResponse->getStatusCode(), WrongStatusException::ERROR_CODE);
        $this->assertEquals($jsonExceptionResponse->getResult(), $message);
    }
}
