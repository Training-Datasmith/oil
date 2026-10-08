<?php

namespace Tests\Oil;

use Tests\Support\OilTestCase;

class ExceptionTest extends OilTestCase
{
	public function testIsThrowableException()
	{
		$e = new \Oil\Exception('boom', 7);
		$this->assertInstanceOf(\Oil\Exception::class, $e);
		$this->assertInstanceOf(\Exception::class, $e);
		$this->assertSame('boom', $e->getMessage());
		$this->assertSame(7, $e->getCode());
	}
}
