<?php

namespace Test\Pagantis\OrdersApiClient;

use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTest
 * @package Test\Pagantis\OrdersApiClient
 */
abstract class AbstractTest extends TestCase
{
    /**
     * @var string
     */
    protected $resourcePath;

    /**
     * AbstractTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->resourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR;
        parent::__construct($name, $data, $dataName);
    }
}
