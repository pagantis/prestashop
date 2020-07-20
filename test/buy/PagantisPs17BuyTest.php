<?php

namespace Test\Buy;

use Test\Common\AbstractPs17Selenium;
use Httpful\Request;
use Pagantis\ModuleUtils\Exception\QuoteNotFoundException;
use Pagantis\ModuleUtils\Exception\MerchantOrderNotFoundException;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17buy
 */
class PagantisPs17BuyTest extends AbstractPs17Selenium
{
    /**
     * config route
     */
    const NOTIFICATION_FOLDER = '/index.php?fc=module&module=pagantis&controller=notify&product=Pagantis&key=xxxxxx';

    /**
     * @throws  \Exception
     */
    public function testBuy()
    {
        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout();
        $this->verifyPagantis();
        $this->checkConcurrency();
        $this->checkPagantisOrderId();
        $this->checkAlreadyProcessed();
        $this->quit();
    }

    /**
     * Check if with a empty parameter called order-received we can get a QuoteNotFoundException
     */
    protected function checkConcurrency()
    {
        $notifyUrl = self::PS17URL.self::NOTIFICATION_FOLDER.'&id_cart=';
        $this->assertNotEmpty($notifyUrl, $notifyUrl);
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertContains(
            QuoteNotFoundException::ERROR_MESSAGE,
            $response->body->result,
            "PR=>".$response->body->result
        );
    }

    /**
     * Check if with a parameter called order-received set to a invalid identification,
     * we can get a NoIdentificationException
     */
    protected function checkPagantisOrderId()
    {
        $orderId=0;
        $notifyUrl = self::PS17URL.self::NOTIFICATION_FOLDER.'&id_cart='.$orderId;
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
            MerchantOrderNotFoundException::ERROR_MESSAGE,
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
        $notifyUrl = self::PS17URL.self::NOTIFICATION_FOLDER.'&id_cart=6';
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertContains(
            MerchantOrderNotFoundException::ERROR_MESSAGE,
            $response->body->result,
            "PR51=>".$response->body->result
        );
    }
}
