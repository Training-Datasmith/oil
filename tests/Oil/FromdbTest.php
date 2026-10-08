<?php

namespace Tests\Oil;

use Fuel\Tasks\Fromdb;
use Tests\Support\OilTestCase;

class FromdbTest extends OilTestCase
{
	public function testFetchTablesStripsPrefixAndSkipsMigration()
	{
		\DB::$table_prefix = 'pre_';
		\Config::set('migrations.table', 'migration');
		\DB::$list_tables = array('pre_posts', 'pre_migration', 'other');
		\Cli::set_option('all', true);
		$out = $this->invokeStaticPrivate(Fromdb::class, 'fetch_tables', array('scaffolding'));
		$this->assertSame(array('posts', 'other'), $out);
	}

	public function testListTablesFailurePrintsDriverMessage()
	{
		$script = TEST_ROOT.'/tests/scripts/fromdb_fetch_tables.php';
		$descriptor = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$proc = proc_open(escapeshellarg(PHP_BINARY).' '.escapeshellarg($script), $descriptor, $pipes, TEST_ROOT);
		$out = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$code = proc_close($proc);
		$this->assertStringContainsString('does not support listing tables', $out);
		$this->assertSame(0, $code);
	}
}
