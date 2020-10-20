<?php

namespace Test\Buy;

use Test\Common\AbstractPs15Selenium;
use Httpful\Request;
use Clearpay\ModuleUtils\Exception\QuoteNotFoundException;
use Clearpay\ModuleUtils\Exception\MerchantOrderNotFoundException;

/**
 * @requires prestashop15install
 * @requires prestashop15register
 *
 * @group prestashop15buy
 */
class ClearpayPs15BuyTest extends AbstractPs15Selenium
{
    /**
     * config route
     */
    const NOTIFICATION_FOLDER = '/index.php?fc=module&module=clearpay&controller=notify&product=CLEARPAY&key=xxxxxx';

    /**
     * @throws  \Exception
     */
    public function testBuy()
    {
        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout();
        $this->verifyClearpay();
        $this->checkConcurrency();
        $this->checkClearpayOrderId();
        $this->checkAlreadyProcessed();
        $this->quit();
    }

    /**
     * Check if with a empty parameter called order-received we can get a QuoteNotFoundException
     */
    protected function checkConcurrency()
    {
        $notifyUrl = self::PS15URL.self::NOTIFICATION_FOLDER.'&id_cart=';
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
    protected function checkClearpayOrderId()
    {
        $orderId=0;
        $notifyUrl = self::PS15URL.self::NOTIFICATION_FOLDER.'&id_cart='.$orderId;
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
        $notifyUrl = self::PS15URL.self::NOTIFICATION_FOLDER.'&id_cart=6';
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
