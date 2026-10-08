<?php

namespace Tests\Oil;

use Oil\Generate;
use Oil\Generate_Scaffold;
use Tests\Support\OilTestCase;

class GenerateScaffoldTest extends OilTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		Generate_Scaffold::_init();
	}

	protected function tearDown(): void
	{
		Generate::$scaffolding = false;
		parent::tearDown();
	}

	public function testUnknownSubfolder()
	{
		$this->expectException(\Oil\Exception::class);
		Generate_Scaffold::forge(array('post', 'title:string'), 'nope');
	}

	public function testScaffoldStyleMigrationMarksTimestampsNullable()
	{
		is_dir(APPPATH.'migrations') or mkdir(APPPATH.'migrations', 0755, true);
		Generate::$scaffolding = true;
		Generate::migration(array(
			'create_posts',
			'title:string[40]',
			'created_at:int:null[1]',
			'updated_at:int:null[1]',
		), false);
		Generate::build();
		$mig = glob(APPPATH.'migrations'.DS.'*_create_posts.php');
		$this->assertNotEmpty($mig);
		$this->assertStringContainsString("'null' => true", file_get_contents($mig[0]));
	}

	public function testFieldsRegex()
	{
		$this->assertSame(1, preg_match(Generate_Scaffold::$fields_regex, 'title:string[40]', $m));
		$this->assertSame('title', $m[1]);
		$this->assertSame('string', $m[2]);
		$this->assertSame('40', $m[4]);
	}
}
