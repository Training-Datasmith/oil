<?php

use Tests\Support\OilTestCase;

class PhpServerTest extends OilTestCase
{
	public function testExistingFileReturnsFalse()
	{
		$docroot = DOCROOT.'phpsrv';
		is_dir($docroot) or mkdir($docroot, 0755, true);
		$existing = $docroot.DS.'exists.php';
		file_put_contents($existing, '<?php return "file";');
		$marker = $docroot.DS.'marker.txt';
		@unlink($marker);
		$result = $this->runRouter(array(
			'DOCUMENT_ROOT' => $docroot,
			'SCRIPT_NAME' => '/exists.php',
		), $marker);
		$this->assertFalse($result);
		$this->assertFalse(is_file($marker));
	}

	public function testMissingScriptIncludesFrontController()
	{
		$docroot = DOCROOT.'phpsrv2';
		is_dir($docroot) or mkdir($docroot, 0755, true);
		$marker = $docroot.DS.'marker.txt';
		@unlink($marker);
		file_put_contents($docroot.DS.'index.php', '<?php file_put_contents(__DIR__."/marker.txt", $_SERVER["SCRIPT_NAME"]);');
		$result = $this->runRouter(array(
			'DOCUMENT_ROOT' => $docroot,
			'SCRIPT_NAME' => '/missing',
		), $marker);
		$this->assertTrue($result);
		$this->assertFileExists($marker);
		$this->assertStringContainsString('phpserver.php', file_get_contents($marker));
	}

	protected function runRouter(array $server, $marker)
	{
		$script = TEST_ROOT.'/tests/scripts/phpserver_runner.php';
		$descriptor = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$cmd = PHP_BINARY.' '.escapeshellarg($script).' '.escapeshellarg(json_encode($server));
		$proc = proc_open($cmd, $descriptor, $pipes, TEST_ROOT);
		$output = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);
		$lines = array_filter(explode("\n", trim($output)));
		$last = trim(end($lines));
		return $last !== 'false';
	}
}
