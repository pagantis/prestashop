<?php

namespace Tests\Pagantis\ModuleUtils\Model\Log;

use PHPUnit\Framework\TestCase;
use Pagantis\ModuleUtils\Model\Log\LogEntry;

class LogEntryTest extends TestCase
{
    /**
     * INFO_MESSAGE
     */
    const INFO_MESSAGE = 'Success message';

    /**
     * ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Error message';

    /**
     * testInfo
     */
    public function testInfo()
    {
        $logEntry = new LogEntry();
        $logEntry->info(self::INFO_MESSAGE);

        $this->assertEquals(self::INFO_MESSAGE, $logEntry->getMessage());
        $this->assertInstanceOf('Pagantis\ModuleUtils\Model\Log\LogEntry', $logEntry);
    }

    /**
     * testError
     *
     * @throws \ReflectionException
     */
    public function testError()
    {
        $logEntry = new LogEntry();
        $logEntry->error(new \Exception(self::INFO_MESSAGE));
        
        $reflectCreateOrderMethod = new \ReflectionClass('Pagantis\ModuleUtils\Model\Log\LogEntry');
        $property = $reflectCreateOrderMethod->getProperty('message');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($logEntry), $logEntry->getMessage());
        
        $property = $reflectCreateOrderMethod->getProperty('code');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($logEntry), $logEntry->getCode());
        
        $property = $reflectCreateOrderMethod->getProperty('line');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($logEntry), $logEntry->getLine());
        
        $property = $reflectCreateOrderMethod->getProperty('file');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($logEntry), $logEntry->getFile());
        
        $property = $reflectCreateOrderMethod->getProperty('trace');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($logEntry), $logEntry->getTrace());

        $this->assertInstanceOf('Pagantis\ModuleUtils\Model\Log\LogEntry', $logEntry);
    }

    /**
     * testToJson
     */
    public function testToJson()
    {
        $logEntry = new LogEntry();
        $logEntry->info(self::INFO_MESSAGE);
        $jsonArray = $logEntry->toJson();

        $this->assertJson($jsonArray);
    }

    /**
     * testJsonSerialize
     */
    public function testJsonSerialize()
    {
        $logEntry = new LogEntry();
        $logEntry->info(self::INFO_MESSAGE);
        $jsonArray = $logEntry->jsonSerialize();

        $this->assertArrayHasKey('message', $jsonArray);
        $this->assertEquals($logEntry->getMessage(), self::INFO_MESSAGE);
    }
}
