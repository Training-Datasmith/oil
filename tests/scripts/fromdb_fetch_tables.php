<?php

error_reporting(-1);
require dirname(__DIR__).'/bootstrap.php';

\DB::$list_tables_exception = new FuelException('db fail');
\Cli::set_option('all', true);

try
{
	\DB::list_tables(null, null);
}
catch (FuelException $e)
{
	\Cli::write('The database driver configured does not support listing tables. Please specify them manually.', 'red');
	echo \Cli::capturedOutput();
	exit(0);
}

exit(1);
