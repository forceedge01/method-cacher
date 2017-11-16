<?php

namespace Genesis\MethodPersister;

function mkdir($path, $permissions, $recursive)
{
    if ($path) {
        return false;
    }

    return true;
}

function file_put_contents($path, $contents)
{
    if ($path) {
        return true;
    }

    return false;
}

function file_exists($path)
{
    if ($path) {
        return true;
    }

    return false;
}

function file_get_contents($path)
{
    if ($path) {
        return json_encode([
            'value' => serialize($path),
            'time' => time()
        ]);
    }

    return false;
}

function time()
{
    return 7736276347;
}

function strtotime($time)
{
    return time() + (int) str_replace(' seconds', '', $time);
}

namespace Genesis\MethodPersister\Tests;

use Genesis\MethodPersister\FilePersistenceRepository;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class FilePersistenceRepositoryTest extends PHPUnit_Framework_TestCase
{
    private $testObject;

    public function setUp()
    {
        $storagePath = './storage/path';

        $this->testObject = new FilePersistenceRepository($storagePath);
    }

    public function testStoragePath()
    {
        $this->assertEquals('./storage/path', $this->getReflectionProperty('centralStoragePath'));
    }

    public function testSetStateDistribute()
    {
        $key = 'unique-key';
        $data = ['wow'];
        $time = '+10 seconds';
        $state = 1;
        $expectedResult = '{"value":"a:1:{i:0;s:3:\"wow\";}","time":7736276357}';

        $result = $this->testObject->set($key, $data, $time, $state);

        $this->assertInstanceOf(get_class($this->testObject), $result);
        $this->assertEquals($expectedResult, $_SESSION['persistentState'][$key]);
    }

    public function testSetStateCentral()
    {
        $key = 'unique-key';
        $data = ['wow'];
        $time = '+10 seconds';
        $state = 2;

        $result = $this->testObject->set($key, $data, $time, $state);

        $this->assertInstanceOf(get_class($this->testObject), $result);
    }

    /**
     * @expectedException Exception
     */
    public function testSetStateCentralException()
    {
        $key = '';
        $data = ['wow'];
        $time = '+10 seconds';
        $state = 2;

        $this->setReflectionProperty('centralStoragePath', null);

        $this->testObject->set($key, $data, $time, $state);
    }

    public function testGetStateDistribute()
    {
        $key = 'random' . time();
        $state = 1;

        $result = $this->testObject->get($key, $state);

        $this->assertFalse($result);
    }

    public function testGetStateDistributeKnownKey()
    {
        $key = 'random';
        $state = 1;
        $expectedResult = ['abc', 132];

        $this->testObject->set($key, $expectedResult, '+10 seconds', $state);

        $result = $this->testObject->get($key, $state);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetStateCentral()
    {
        $key = 'unique';
        $state = 2;
        $expectedResult = './central/unique';

        $this->setReflectionProperty('centralStoragePath', './central/');

        $result = $this->testObject->get($key, $state);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetStateCentralFileNotExists()
    {
        $key = '';
        $state = 2;

        $this->setReflectionProperty('centralStoragePath', null);

        $result = $this->testObject->get($key, $state);

        $this->assertFalse($result);
    }

    private function getReflectionProperty($property)
    {
        $reflection = new ReflectionClass(get_class($this->testObject));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($this->testObject);
    }

    private function setReflectionProperty($property, $value)
    {
        $reflection = new ReflectionClass(get_class($this->testObject));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->setValue($this->testObject, $value);
    }
}
