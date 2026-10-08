<?php

namespace Tests\Oil;

use Oil\Generate;
use Tests\Support\OilTestCase;

class GenerateConfigTest extends OilTestCase
{
	public function testMissingName()
	{
		$this->expectException(\Oil\Exception::class);
		Generate::config(array(''));
	}

	public function testWritesMergedConfigAppOverridesCore()
	{
		is_dir(COREPATH.'config') or mkdir(COREPATH.'config', 0755, true);
		is_dir(APPPATH.'config') or mkdir(APPPATH.'config', 0755, true);
		file_put_contents(COREPATH.'config'.DS.'app.php', "<?php return array('foo' => 'from-core', 'bar' => 'only-core');\n");
		file_put_contents(APPPATH.'config'.DS.'app.php', "<?php return array('foo' => 'from-app');\n");
		\Finder::set_search('config', 'app', array(APPPATH.'config'.DS.'app.php', COREPATH.'config'.DS.'app.php'));
		\Cli::set_option('overwrite', true);
		Generate::config(array('app', 'zip:1'));
		$content = file_get_contents(APPPATH.'config'.DS.'app.php');
		$this->assertStringContainsString("'foo' => 'from-app'", $content);
		$this->assertStringContainsString("'bar' => 'only-core'", $content);
		$this->assertStringContainsString("'zip' => '1'", $content);
	}
}
