<?php

error_reporting(-1);
require dirname(__DIR__).'/bootstrap.php';

$host = $argv[1];
$port = $argv[2];
\Config::set('package', array('sources' => array($host.':'.$port)));
\Cli::set_option('direct', true);
\Cli::set_option('version', 'master');

try
{
	\Oil\Package::install('demopkg');
}
catch (\Throwable $e)
{
	fwrite(STDERR, $e->getMessage());
	exit(1);
}
