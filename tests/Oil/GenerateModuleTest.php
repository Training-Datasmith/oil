<?php

namespace Tests\Oil;

use Oil\Generate;
use Tests\Support\OilTestCase;

class GenerateModuleTest extends OilTestCase
{
	public function testModuleWithoutFoldersOption()
	{
		\Config::set('module_paths', array(APPPATH.'modules'.DS));
		Generate::module(array('blog'));
		Generate::build();
		$this->assertDirectoryExists(APPPATH.'modules'.DS.'blog'.DS.'classes');
	}
}
