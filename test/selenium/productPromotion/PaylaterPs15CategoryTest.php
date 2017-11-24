<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs15CategoryTest
 * @package Test\Selenium\ProductPromotion
 *
 * @group prestashop15category
 * @group install
 */
class PaylaterPs15CategoryTest extends PaylaterPrestashopTest
{
    const PROMOTIONS_CATEGORY = 'paylater-promotion-product';
    /**
     * test category exists on prestashop 15
     */
    public function testInstallAndConfigurePaylaterInPrestashop15()
    {
        $this->loginToBackOffice();
        $this->goToProductCategories();
        $this->checkPaylaterDiscount();
        $this->assignToProduct();
        $this->checkProductHasPromotionAndSentence();
        $this->quit();
    }

    /**
     * Login to the backoffice
     */
    public function loginToBackOffice()
    {
        $this->webDriver->get(self::PS15URL.self::BACKOFFICE_FOLDER);
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
     * Install PaylaterModule
     */
    public function goToProductCategories()
    {
        $this->findByLinkText('Catalog')->click();
        $this->findByLinkText('Categories')->click();
    }

    /**
     * Check paylater discount and visible
     */
    public function checkPaylaterDiscount()
    {
        $category = WebDriverBy::className('category');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $category,
            self::PROMOTIONS_CATEGORY
        );
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
        $product = WebDriverBy::id('tr__1_0');
        $condition = WebDriverExpectedCondition::elementToBeClickable($product);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($product)->click();
        $associations = WebDriverBy::id('link-Associations');
        $condition = WebDriverExpectedCondition::elementToBeClickable($associations);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($associations)->click();
        $search = WebDriverBy::id('search_cat');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($search);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($search)->clear()->sendKeys(self::PROMOTIONS_CATEGORY);
        $results = WebDriverBy::className('ac_results');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($results);
        $this->waitUntil($condition);
        $this->findById('search_cat')->clear()->sendKeys(WebDriverKeys::ENTER);
        $this->findById('desc-product-save')->click();
    }

    /**
     * Check product has promo, sentence and discount
     */
    public function checkProductHasPromotionAndSentence()
    {
        $this->webDriver->get(self::PS15URL);
        $featuredProductCenterSearch = WebDriverBy::id('featured-products_block_center');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($featuredProductCenterSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $product = $featuredProductCenterSearch->className('s_title_block');
        $this->webDriver->findElement($product)->click();
        $addToCartSearch = WebDriverBy::id('add_to_cart');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($addToCartSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
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
