<?php

namespace Tests\Oil;

use Oil\Generate;
use Tests\Support\OilTestCase;

class GenerateViewsTest extends OilTestCase
{
	public function testWithTestOneFilePerAction()
	{
		\Cli::set_option('with-test', true);
		Generate::views(array('blog', 'index', 'edit'), 'orm');
		Generate::build();
		$index = APPPATH.'tests'.DS.'view'.DS.'blog'.DS.'index.php';
		$edit = APPPATH.'tests'.DS.'view'.DS.'blog'.DS.'edit.php';
		$this->assertFileExists($index);
		$this->assertFileExists($edit);
		$this->assertStringContainsString('Test_View_blog_Index', file_get_contents($index));
		$this->assertStringContainsString('Test_View_blog_Edit', file_get_contents($edit));
	}
}
