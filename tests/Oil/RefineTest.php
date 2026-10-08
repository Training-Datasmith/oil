<?php

namespace Tests\Oil;

use Oil\Refine;
use Tests\Support\OilTestCase;

class RefineTest extends OilTestCase
{
	protected function writeTask($name, $body)
	{
		is_dir(APPPATH.'tasks') or mkdir(APPPATH.'tasks', 0755, true);
		$path = APPPATH.'tasks'.DS.$name.'.php';
		file_put_contents($path, $body);
		\Finder::set_search('tasks', $name, $path);
		\Finder::set_list_files('tasks', array($path));
		return $path;
	}

	public function testEmptyAndHelpReturnHelp()
	{
		Refine::run('');
		$this->assertStringContainsString('php oil [r|refine]', \Cli::capturedOutput());
	}

	public function testFarNameHasNoSuggestion()
	{
		$this->writeTask('alpha', "<?php namespace Fuel\\Tasks; class Alpha { public function run() {} }");
		$this->expectException(\Oil\Exception::class);
		$this->expectExceptionMessage('Task "zzzzzzzzzz" does not exist.');
		Refine::run('zzzzzzzzzz');
	}

	public function testCloseNameSuggests()
	{
		$this->writeTask('alpha', "<?php namespace Fuel\\Tasks; class Alpha { public function run() {} }");
		try
		{
			Refine::run('alphb');
		}
		catch (\Oil\Exception $e)
		{
			$this->assertStringContainsString('Did you mean "alpha"?', $e->getMessage());
			return;
		}
		$this->fail('Expected exception');
	}

	public function testRunsTaskAndPrintsReturn()
	{
		$this->writeTask('bravo', "<?php namespace Fuel\\Tasks; class Bravo { public function run() { return 'bravo-ran'; } }");
		Refine::run('bravo');
		$this->assertStringContainsString('bravo-ran', \Cli::capturedOutput());
	}

	public function testHelpTwiceLoadsTasksOnce()
	{
		$this->writeTask('alphatwo', "<?php namespace Fuel\\Tasks; class Alphatwo { public function run() {} public function help() { \\Cli::write('help-ok'); } }");
		Refine::run('alphatwo:help');
		Refine::run('alphatwo:help');
		$this->assertStringContainsString('help-ok', \Cli::capturedOutput());
	}
}
