<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

namespace Test\Paylater;

use PHPUnit\Framework\TestCase;

/**
 * Class DockerTest
 */
class DockerTest extends TestCase
{
    /**
     * Presatshop docker running in local and in travis
     */
    const DOCKER_PRESTASHOP = 'http://localhost';

    /**
     * BackOffice URi for prestashop
     */
    const BACKOFFICE_DIR = '/adminTest';

    /**
     * Test docker is running:
     */
    public function testHomePage()
    {
        if (file_exists('/.dockerenv')) {
            $homePage = file_get_contents(self::DOCKER_PRESTASHOP);

            $this->assertNotEmpty($homePage);
        }
    }

    /**
     * Test docker is running in BackOffice
     */
    public function testBackOffice()
    {
        if (file_exists('/.dockerenv')) {
            $backOffice = file_get_contents(self::DOCKER_PRESTASHOP . self::BACKOFFICE_DIR);

            $this->assertNotEmpty($backOffice);
        }
    }
}
