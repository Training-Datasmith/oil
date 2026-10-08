<?php

namespace Tests\Oil;

use Oil\Command;
use Oil\Generate;
use Tests\Support\FuelTestEnvironment;
use Tests\Support\OilTestCase;

class CommandTest extends OilTestCase
{
	public function testNoArgsPrintsHelp()
	{
		ob_start();
		Command::init(array('oil'));
		$out = ob_get_clean();
		$this->assertStringContainsString('php oil [console|generate|package|refine|help|server|test]', $out);
	}

	public function testVersionPrintsFuelVersionAndEnv()
	{
		\Cli::set_option('version', true);
		Command::init(array('oil'));
		$this->assertStringContainsString('Fuel: 1.9-test running in "development" mode', \Cli::capturedOutput());
	}

	public function testUnknownSubcommandPrintsHelp()
	{
		ob_start();
		Command::init(array('oil', 'nope'));
		$out = ob_get_clean();
		$this->assertStringContainsString('php oil [console|generate|package|refine|help|server|test]', $out);
	}

	public function testCreateRefuses()
	{
		Command::init(array('oil', 'create'));
		$this->assertStringContainsString('You can not use "oil create"', \Cli::capturedOutput());
	}

	public function testGenerateHelp()
	{
		Command::init(array('oil', 'g'));
		$this->assertStringContainsString('php oil [g|generate]', \Cli::capturedOutput());
	}

	public function testConsoleServerTestPackageHelp()
	{
		Command::init(array('oil', 'console', 'help'));
		$this->assertStringContainsString('php oil [c|console]', \Cli::capturedOutput());
		\Cli::reset();
		Command::init(array('oil', 'server', 'help'));
		$this->assertStringContainsString('--port', \Cli::capturedOutput());
		\Cli::reset();
		Command::init(array('oil', 'test', 'help'));
		$this->assertStringContainsString('--group', \Cli::capturedOutput());
		\Cli::reset();
		Command::init(array('oil', 'package'));
		$this->assertStringContainsString('php oil package install', \Cli::capturedOutput());
	}

	public function testClearArgsTrimsAndRepacks()
	{
		$result = $this->invokeStaticPrivate(Command::class, '_clear_args', array(array('oil', '-f', ' g ', '--quiet')));
		$this->assertSame(array('oil', 'g'), $result);
	}

	public function testFlagBeforeSubcommandStillGenerates()
	{
		\Cli::set_option('overwrite', true);
		\Finder::set_search('config', 'app', array(COREPATH.'config'.DS.'app.php'));
		is_dir(COREPATH.'config') or mkdir(COREPATH.'config', 0755, true);
		file_put_contents(COREPATH.'config'.DS.'app.php', "<?php return array('foo' => 'from-core');\n");
		is_dir(APPPATH.'config') or mkdir(APPPATH.'config', 0755, true);
		Command::init(array('oil', '-q', 'g', 'config', 'app', 'foo:bar'));
		$path = APPPATH.'config'.DS.'app.php';
		$this->assertFileExists($path);
		$this->assertStringContainsString("'foo' => 'bar'", file_get_contents($path));
	}

	public function testPrintExceptionHidesTraceInProduction()
	{
		\Fuel::$env = \Fuel::PRODUCTION;
		\Cli::reset();
		$this->invokeStaticPrivate(Command::class, 'print_exception', array(new \Exception('cannot be uninstalled')));
		$this->assertStringNotContainsString('Callstack:', \Cli::capturedErrors());
		\Fuel::$env = 'development';
		\Cli::reset();
		$this->invokeStaticPrivate(Command::class, 'print_exception', array(new \Exception('cannot be uninstalled')));
		$this->assertStringContainsString('Callstack:', \Cli::capturedErrors());
	}
}
