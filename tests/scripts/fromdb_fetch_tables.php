<?php

error_reporting(-1);
putenv('OIL_TEST_ECHO_CLI=1');
require dirname(__DIR__).'/bootstrap.php';

\DB::$list_tables_exception = new FuelException('db fail');
\Cli::set_option('all', true);
\Config::set('migrations.table', 'migration');

$ref = new ReflectionMethod('Fuel\\Tasks\\Fromdb', 'fetch_tables');
$ref->setAccessible(true);
$ref->invoke(null, 'scaffolding');
