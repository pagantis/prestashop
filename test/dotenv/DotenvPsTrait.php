<?php

namespace Test\Dotenv;

/**
 * Trait DotenvPs15Trait
 *
 * @package Test\Dotenv
 */
trait DotenvPsTrait
{
    /**
     * @return array
     */
    public function getProperties()
    {
        return array(
            'PMT_TITLE' => '\'Instant Financing\'',
            'PMT_SIMULATOR_DISPLAY_TYPE' => 6,
            'PMT_SIMULATOR_DISPLAY_POSITION' => 'hookDisplayProductButtons',
            'PMT_SIMULATOR_START_INSTALLMENTS' => 3,
            'PMT_SIMULATOR_MAX_INSTALLMENTS' => 12,
            'PMT_FORM_DISPLAY_TYPE' => 0,
            'PMT_DISPLAY_MIN_AMOUNT' => 1,
            'PMT_URL_OK' => '',
            'PMT_URL_KO' => '',
        );
    }

    /**
     * @param array  $properties
     * @param string $container
     */
    public function saveDotEnvFile($properties = array(), $psVersion = '15')
    {
        $this->assertTrue(is_writable(__DIR__.'/../../.env'));

        $file = '';
        foreach ($properties as $key => $value) {
            $file .= $key . '=' . $value . PHP_EOL;
        }

        $fp = fopen('/tmp/.env', 'w');
        fwrite($fp, $file);
        fclose($fp);

        $command = 'docker cp /tmp/.env prestashop' . $psVersion . ':/var/www/html/modules/paylater/.env 2>&1';
        $execResult = exec($command);
        $this->assertEmpty($execResult);
    }
}
