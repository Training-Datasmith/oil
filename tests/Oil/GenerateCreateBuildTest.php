<?php

namespace Tests\Oil;

use Oil\Generate;
use Tests\Support\OilTestCase;

class GenerateCreateBuildTest extends OilTestCase
{
	public function testBuildWritesQueuedFile()
	{
		$path = APPPATH.'foo'.DS.'bar.txt';
		Generate::create($path, 'hello', 'file');
		Generate::build();
		$this->assertFileExists($path);
		$this->assertSame('hello', file_get_contents($path));
	}

	public function testExistingFileRequiresForce()
	{
		$path = APPPATH.'dup.txt';
		Generate::create($path, 'one', 'file');
		Generate::build();
		$this->expectException(\Oil\Exception::class);
		Generate::create($path, 'two', 'file');
	}

	public function testClassName()
	{
		$this->assertSame('Foo_Bar', Generate::class_name('foo bar'));
		$this->assertSame('Foo_Bar', Generate::class_name('foo_bar'));
	}
}
