<?php

use PHPUnit_Framework_TestCase;
use Genesis\MethodPersister\Persister;

class PersisterTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$storagePath = '';

		$this->testObject = new Persister($storagePath);
	}

	public function testPersist()
	{
		$this->markTestIncomplete('whatever');
	}
}