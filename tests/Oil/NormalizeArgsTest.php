<?php

namespace Tests\Oil;

use Oil\Generate;
use Tests\Support\OilTestCase;

class NormalizeArgsTest extends OilTestCase
{
	public function testStringDefaults()
	{
		$out = Generate::normalize_args(array('title:string'));
		$this->assertSame('varchar', $out['title']['data_type']);
		$this->assertSame('255', $out['title']['constraint']);
		$this->assertFalse($out['title']['null']);
		$this->assertArrayHasKey('id', $out);
		$this->assertTrue($out['id']['auto_increment']);
	}

	public function testBracketedNullOne()
	{
		$out = Generate::normalize_args(array('title:int:null[1]'));
		$this->assertSame('int', $out['title']['data_type']);
		$this->assertTrue($out['title']['null']);
		$this->assertArrayNotHasKey('1', $out['title']);
	}

	public function testBracketedNullZero()
	{
		$out = Generate::normalize_args(array('title:int:null[0]'));
		$this->assertFalse($out['title']['null']);
	}

	public function testDecimalAndEnum()
	{
		$out = Generate::normalize_args(array('price:decimal[10,2]', 'status:enum[a,b]'));
		$this->assertSame('10,2', $out['price']['constraint']);
		$this->assertStringContainsString('"a"', $out['status']['constraint']);
	}

	public function testNoStandardisationSkipsIdAndTimestamps()
	{
		\Cli::set_option('no-standardisation', true);
		$out = Generate::normalize_args(array('title:string'));
		$this->assertArrayNotHasKey('id', $out);
		$this->assertArrayNotHasKey('created_at', $out);
	}
}
