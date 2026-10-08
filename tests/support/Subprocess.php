<?php

namespace Tests\Support;

class Subprocess
{
	public static function run(array $argv, $env = array())
	{
		$script = tempnam(sys_get_temp_dir(), 'oil-sub');
		$code = '<?php
error_reporting(-1);
$_SERVER["argv"] = '.var_export($argv, true).';
require "'.addslashes(TEST_ROOT.'/tests/bootstrap.php').'";
';
		file_put_contents($script, $code);
		$descriptor = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$proc = proc_open(
			PHP_BINARY.' '.escapeshellarg($script),
			$descriptor,
			$pipes,
			TEST_ROOT,
			$env
		);
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$code = proc_close($proc);
		unlink($script);
		return array('code' => $code, 'out' => $stdout.$stderr);
	}
}
