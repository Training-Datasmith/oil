<?php

namespace Tests\Oil;

use Oil\Console;
use Tests\Support\OilTestCase;

class ConsoleTest extends OilTestCase
{
	public function testHelpText()
	{
		Console::help();
		$this->assertStringContainsString('php oil [c|console]', \Cli::capturedOutput());
	}

	public function testHistoryPushPopAndCap()
	{
		$c = (new \ReflectionClass(Console::class))->newInstanceWithoutConstructor();
		for ($i = 0; $i < 100; $i++)
		{
			$this->invokePrivate($c, 'push_history', array('line'.$i));
		}
		$hist = $this->invokePrivate($c, 'show_history');
		$this->assertNull($hist);
		$ref = new \ReflectionProperty($c, 'history');
		$ref->setAccessible(true);
		$history = $ref->getValue($c);
		$this->assertCount(99, $history);
		$this->assertStringEndsWith(';', $history[0]);
		$this->assertStringNotContainsString('line0;', implode('', $history));
		$this->invokePrivate($c, 'pop_history');
		$history = $ref->getValue($c);
		$this->assertStringNotContainsString('line99;', implode('', $history));
	}

	public function testIsImmediate()
	{
		$this->assertTrue($this->invokeStaticPrivate(Console::class, 'is_immediate', array('1+1')));
		$this->assertTrue($this->invokeStaticPrivate(Console::class, 'is_immediate', array('foo()')));
		$this->assertFalse($this->invokeStaticPrivate(Console::class, 'is_immediate', array('echo 1')));
		$this->assertFalse($this->invokeStaticPrivate(Console::class, 'is_immediate', array('$a = 1')));
		$this->assertFalse($this->invokeStaticPrivate(Console::class, 'is_immediate', array('return 1')));
	}

	public function testTabCompleteIncludesKnownSymbols()
	{
		$list = Console::tab_complete('', 0, 0);
		$this->assertContains('PHP_VERSION', $list);
		$this->assertContains('strlen', $list);
		foreach ($list as $item)
		{
			$this->assertIsString($item);
		}
	}

	public function testBuildDateMatchesPhpinfoLine()
	{
		ob_start();
		phpinfo(INFO_GENERAL);
		$info = strip_tags(ob_get_clean());
		$date = $this->invokeStaticPrivate(Console::class, 'build_date');
		$this->assertNotSame('???', $date);
		$this->assertNotEmpty($date);
		$this->assertStringContainsString($date, $info);
	}

	public function testImmediateExpressionPrintsValue()
	{
		$this->assertTrue($this->invokeStaticPrivate(Console::class, 'is_immediate', array('1+1')));
	}
}
