<?php

namespace Test\Buy;

use Test\Common\AbstractPs16Selenium;
use Httpful\Request;
use PagaMasTarde\ModuleUtils\Exception\AlreadyProcessedException;
use PagaMasTarde\ModuleUtils\Exception\NoIdentificationException;
use PagaMasTarde\ModuleUtils\Exception\QuoteNotFoundException;

/**
 * @requires prestashop16install
 * @requires prestashop16register
 *
 * @group prestashop16buy
 */
class PaylaterPs16BuyTest extends AbstractPs16Selenium
{
    /**
     * config route
     */
    const NOTIFICATION_FOLDER = '/index.php?fc=module&module=paylater&controller=notify';

    /**
     * @throws  \Exception
     */
    public function testBuy()
    {
//        $this->loginToFrontend();
//        $this->goToProduct();
//        $this->addProduct();
//        $this->goToCheckout();
//        $this->verifyPaylater();
        $this->checkConcurrency();
        $this->checkPmtOrderId();
        $this->checkAlreadyProcessed();
        $this->quit();
    }

    /**
     * Check if with a empty parameter called order-received we can get a QuoteNotFoundException
     */
    protected function checkConcurrency()
    {
        $notifyUrl = self::PS16URL.self::NOTIFICATION_FOLDER.'&order=';
        var_dump($notifyUrl);
        $this->assertNotEmpty($notifyUrl, $notifyUrl);
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertContains(QuoteNotFoundException::ERROR_MESSAGE, $response->body->result, "PR=>".$response->body->result);
    }
    /**
     * Check if with a parameter called order-received set to a invalid identification, we can get a NoIdentificationException
     */
    protected function checkPmtOrderId()
    {
        $orderId=0;
        $notifyUrl = self::PS16URL.self::NOTIFICATION_FOLDER.'?order='.$orderId;
        $this->assertNotEmpty($notifyUrl, $notifyUrl);
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertEquals(
            $response->body->merchant_order_id,
            $orderId,
            $response->body->merchant_order_id.'!='. $orderId
        );
        $this->assertContains(
            NoIdentificationException::ERROR_MESSAGE,
            $response->body->result,
            "PR=>".$response->body->result
        );
    }
    /**
     * Check if re-launching the notification we can get a AlreadyProcessedException
     *
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    protected function checkAlreadyProcessed()
    {
        $notifyUrl = self::PS16URL.self::NOTIFICATION_FOLDER.'?order=145000008';
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertNotEmpty($response->body->merchant_order_id, $response);
        $this->assertNotEmpty($response->body->pmt_order_id, $response);
        $this->assertContains(AlreadyProcessedException::ERROR_MESSAGE, $response->body->result, "PR51=>".$response->body->result);
    }
}
