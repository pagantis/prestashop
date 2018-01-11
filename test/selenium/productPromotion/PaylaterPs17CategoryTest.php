<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs17CategoryTest
 * @package Test\Selenium\ProductPromotion
 *
 * @group prestashop17category
 * @group install
 */
class PaylaterPs17CategoryTest extends PaylaterPrestashopTest
{
    const PROMOTIONS_CATEGORY = 'paylater-promotion-product';
    /**
     * test category exists on prestashop 17
     */
    public function testInstallAndConfigurePaylaterInPrestashop17()
    {
        $this->loginToBackOffice();
        $this->assignToProduct();
        $this->checkProductHasPromotionAndSentence();
        $this->quit();
    }

    /**
     * Login to the backoffice
     */
    public function loginToBackOffice()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);
        $emailElementSearch = WebDriverBy::id('email');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($emailElementSearch);
        $this->waitUntil($condition);
        $this->findById('email')->clear()->sendKeys($this->configuration['username']);
        $this->findById('passwd')->clear()->sendKeys($this->configuration['password']);
        $this->findById('login_form')->submit();
        $emailElementSearch = WebDriverBy::id('employee_infos');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($emailElementSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Assign Category to product
     */
    public function assignToProduct()
    {
        $this->findByLinkText('Catalog')->click();
        $this->findByLinkText('Products')->click();
        $product   = WebDriverBy::partialLinkText('Printed Chiffon Dress');
        $condition = WebDriverExpectedCondition::elementToBeClickable($product);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($product)->click();
        $this->webDriver->executeScript('document.getElementsByName(\'form[step1][categories][tree][]\')[10].click();');
        $this->findByClass('js-btn-save')->click();
    }

    /**
     * Check product has promo, sentence and discount
     */
    public function checkProductHasPromotionAndSentence()
    {
        $this->webDriver->get(self::PS17URL);
        $featuredProductCenterSearch = WebDriverBy::id('category-3');
        $condition                   = WebDriverExpectedCondition::visibilityOfElementLocated(
            $featuredProductCenterSearch
        );
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($featuredProductCenterSearch)->click();
        try {
            $this->findByLinkText('Printed Chiffon Dress')->click();
        } catch (\Exception $exception) {
            $this->findByLinkText('Gasa')->click();
        }
        $available = WebDriverBy::id('product-availability');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($available);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $pmtSimulator = WebDriverBy::className('PmtSimulator');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($pmtSimulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $discount = $this->webDriver->findElement($pmtSimulator)->getAttribute('data-pmt-discount');
        $this->assertSame('1', $discount);
        $sentence = WebDriverBy::id('pmt-promotion-extra');
        /** @var WebDriverExpectedCondition $condition */
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $sentence,
            $this->configuration['extra']
        );
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }
}
