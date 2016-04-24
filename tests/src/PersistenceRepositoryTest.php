<?php

namespace Genesis\MethodPersister\Tests;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use Genesis\MethodPersister\PersistenceRepository;

class PersistenceRepositoryTest extends PHPUnit_Framework_TestCase
{
	private $testObject;

	public function setUp()
	{
		$storagePath = './storage/path';

		$this->testObject = new PersistenceRepository($storagePath);
	}

	private function getReflectionProperty($property)
	{
		$reflection = new ReflectionClass(get_class($this->testObject));
		$reflectionProperty = $reflection->getProperty($property);
		$reflectionProperty->setAccessible(true);

		return $reflectionProperty->getValue($this->testObject);
	}
}