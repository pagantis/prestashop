<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs16CategoryTest
 * @package Test\Selenium\ProductPromotion
 *
 * @group prestashop16category
 * @group install
 */
class PaylaterPs16CategoryTest extends PaylaterPrestashopTest
{
    const PROMOTIONS_CATEGORY = 'paylater-promotion-product';
    /**
     * test category exists on prestashop 16
     */
    public function testInstallAndConfigurePaylaterInPrestashop16()
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
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);
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
        $product   = WebDriverBy::id('tr__1_0');
        $condition = WebDriverExpectedCondition::elementToBeClickable($product);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($product)->click();
        $associations = WebDriverBy::id('link-Associations');
        $condition    = WebDriverExpectedCondition::elementToBeClickable($associations);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($associations)->click();
        $search    = WebDriverBy::id('associated-categories-tree-categories-search');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($search);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($search)->clear()->sendKeys('paylater');
        $results   = WebDriverBy::className('tt-suggestions');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($results);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->executeScript('document.getElementsByClassName(\'tt-suggestion\')[0].click()');
        $this->findById('product_form')->submit();
    }

    /**
     * Check product has promo, sentence and discount
     */
    public function checkProductHasPromotionAndSentence()
    {
        $this->webDriver->get(self::PS16URL);
        $featuredProductCenterSearch = WebDriverBy::id('center_column');
        $condition                   = WebDriverExpectedCondition::visibilityOfElementLocated(
            $featuredProductCenterSearch
        );
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        try {
            $this->findByLinkText('Camiseta')->click();
        } catch (\Exception $exception) {
            $this->findByLinkText('T-shirt')->click();
        }
        $available = WebDriverBy::id('availability_statut');
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
