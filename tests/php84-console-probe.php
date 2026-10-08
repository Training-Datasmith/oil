<?php

error_reporting(-1);
require __DIR__.'/bootstrap.php';

\Cli::queue_input(':q');
if (ob_get_level() > 0)
{
	ob_start();
}
new \Oil\Console();
while (ob_get_level() > 0)
{
	ob_end_clean();
}

if (error_reporting() !== E_ALL)
{
	fwrite(STDERR, 'expected E_ALL on PHP 8.4+, got '.error_reporting()."\n");
	exit(1);
}

echo "ok\n";
