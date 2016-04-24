<?php

namespace Genesis\MethodPersister;

function call_user_func_array(array $callable, array $params)
{
	return [$params];
}

namespace Genesis\MethodPersister\Tests;

use PHPUnit_Framework_TestCase;
use Genesis\MethodPersister\Persister;
use Genesis\MethodPersister\Interfaces\PersistenceRepositoryInterface;
use DateTime;
use ReflectionClass;

class PersisterTest extends PHPUnit_Framework_TestCase
{
	private $persistenceRepositoryMock;

	private $testObject;

	public function setUp()
	{
		$this->persistenceRepositoryMock = $this->getMockBuilder(PersistenceRepositoryInterface::class)
			->disableOriginalConstructor()
			->getMock();

		$this->testObject = new Persister($this->persistenceRepositoryMock);
	}

	public function testPersist()
	{
		$object = $this;
		$method = 'A method';

		$result = $this->testObject->persist($object, $method);

		$this->assertInstanceOf(Persister::class, $result);

		$this->assertEquals($object, $this->getReflectionProperty('obj'));
		$this->assertEquals($method, $this->getReflectionProperty('on'));
		$this->assertEquals([], $this->getReflectionProperty('args'));
	}

	public function testOverAPeriodOf()
	{
		$time = new DateTime();

		$this->testObject->overAPeriodOf($time);

		$this->assertEquals($time, $this->getReflectionProperty('over'));
	}

	public function testIn()
	{
		$state = 15;

		$this->testObject->in($state);

		$this->assertEquals($state, $this->getReflectionProperty('in'));
	}

	public function testWithParameters()
	{
		$params = [25, 'abc'];

		$this->testObject->withParameters(25, 'abc');

		$this->assertEquals($params, $this->getReflectionProperty('args'));
	}

	public function testExecuteValueReturned()
	{
		$expectedReturn = ['abc', 123];
		$expectedKey = 'Genesis\MethodPersister\Tests\PersisterTest::randomize::a:2:{i:0;i:25;i:1;s:3:"abc";}';

		$this->persistenceRepositoryMock->expects($this->once())
			->method('get')
			->with($expectedKey, Persister::STATE_DISTRIBUTE)
			->will($this->returnValue($expectedReturn));

		$result = $this->testObject
			->persist($this, 'randomize')
			->withParameters(25, 'abc')
			->in(Persister::STATE_DISTRIBUTE)
			->overAPeriodOf('+10 seconds')
			->execute();

		$this->assertEquals($expectedReturn, $result);
	}

	public function testExecuteNoValueReturned()
	{
		$expectedReturn = [25, 'abc'];
		$expectedKey = 'Genesis\MethodPersister\Tests\PersisterTest::randomize::a:2:{i:0;i:25;i:1;s:3:"abc";}';
		$time = '+10 seconds';

		$this->persistenceRepositoryMock->expects($this->once())
			->method('get')
			->with($expectedKey, Persister::STATE_DISTRIBUTE)
			->will($this->returnValue(null));

		$this->persistenceRepositoryMock->expects($this->once())
			->method('set')
			->with($expectedKey, [$expectedReturn], $time, Persister::STATE_DISTRIBUTE)
			->will($this->returnValue($expectedReturn));

		$result = $this->testObject
			->persist($this, 'randomize')
			->withParameters(25, 'abc')
			->in(Persister::STATE_DISTRIBUTE)
			->overAPeriodOf($time)
			->execute();

		$this->assertEquals([$expectedReturn], $result);
	}

	private function getReflectionProperty($property)
	{
		$reflection = new ReflectionClass(get_class($this->testObject));
		$reflectionProperty = $reflection->getProperty($property);
		$reflectionProperty->setAccessible(true);

		return $reflectionProperty->getValue($this->testObject);
	}
}