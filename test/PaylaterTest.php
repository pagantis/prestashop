<?php

namespace Test\Paylater;

use PHPUnit\Framework\TestCase;

/**
 * Class Paylater
 */
class PaylaterTest extends TestCase
{
    /**
     * Presatshop docker running in local and in travis
     */
    const DOCKER_PRESTASHOP = 'http://prestashop.docker:84';

    /**
     * BackOffice URi for prestashop
     */
    const BACKOFFICE_DIR = '/adminTest';

    /**
     * Test docker is running:
     */
    public function testHomePage()
    {
        $homePage = file_get_contents(self::DOCKER_PRESTASHOP);

        $this->assertNotEmpty($homePage);
    }

    /**
     * Test docker is running in BackOffice
     */
    public function testBackOffice()
    {
        $backOffice = file_get_contents(self::DOCKER_PRESTASHOP . self::BACKOFFICE_DIR);

        $this->assertNotEmpty($backOffice);
    }
}
