<?php

namespace Tests\Support;

abstract class OilTestCase extends \PHPUnit\Framework\TestCase
{
	protected $savedErrorReporting;
	protected $savedIni = array();

	protected function setUp(): void
	{
		parent::setUp();
		$this->savedErrorReporting = error_reporting();
		error_reporting(-1);
		FuelTestEnvironment::resetAll();
	}

	protected function tearDown(): void
	{
		error_reporting($this->savedErrorReporting);
		foreach ($this->savedIni as $key => $val)
		{
			ini_set($key, $val);
		}
		parent::tearDown();
	}

	protected function saveIni($key)
	{
		$this->savedIni[$key] = ini_get($key);
	}

	protected function invokePrivate($object, $method, array $args = array())
	{
		$ref = new \ReflectionMethod($object, $method);
		$ref->setAccessible(true);
		return $ref->invokeArgs($object, $args);
	}

	protected function invokeStaticPrivate($class, $method, array $args = array())
	{
		$ref = new \ReflectionMethod($class, $method);
		$ref->setAccessible(true);
		return $ref->invokeArgs(null, $args);
	}
}
