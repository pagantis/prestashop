<?php

namespace Test\Selenium\Install;

/**
 * Class WebTest
 * @package Test\Selenium\Basic
 */
class WebTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * Configure selenium
     */
    protected function setUp()
    {
        $this->setBrowser('chrome');
        $this->setBrowserUrl('http://localhost:4444');
    }

    /**
     * testInstallPaylaterInPrestashop17
     *
     * @group ps17
     */
    public function testInstallPaylaterInPrestashop17()
    {
        $username = 'demo@prestashop.com';
        $password = 'prestashop_demo';
        $backOfficeLoginUrl = 'http://prestashop17/adminTest';

        $this->url($backOfficeLoginUrl);

        $this->byName('email')->value($username);
        $this->byName('passwd')->value($password);
        $this->byName('submitLogin')->click();

        sleep(3);

        $content = $this->byTag('h2')->text();
        $this->assertSame('Dashboard', $content);
        $this->byId('subtab-AdminParentModulesSf')->click();

        sleep(3);

        $this->byId('page-header-desc-configuration-add_module')->click();
        $this->file('Paylater.zip');
        $this->byId('importDropzone')->submit();

        sleep(10);

        $content = $this->byTag('body')->text();
        var_dump($content);
    }
}
