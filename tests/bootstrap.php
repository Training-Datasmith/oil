<?php

error_reporting(-1);
ini_set('display_errors', '1');

define('TEST_ROOT', dirname(__DIR__));
define('OIL_ROOT', TEST_ROOT);

if (is_file(TEST_ROOT.'/vendor/autoload.php'))
{
	require TEST_ROOT.'/vendor/autoload.php';
}

require __DIR__.'/support/fuel_stubs.php';
require __DIR__.'/support/FuelTestEnvironment.php';
require __DIR__.'/support/LazyView.php';
require __DIR__.'/support/OilTestCase.php';
require __DIR__.'/support/Subprocess.php';

if ( ! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

$tmpRoot = getenv('OIL_TEST_TMP');
if ($tmpRoot === false || $tmpRoot === '')
{
	$tmp = sys_get_temp_dir().DS.'oil-tests-'.getmypid();
	putenv('OIL_TEST_TMP='.$tmp);
}
else
{
	$tmp = rtrim($tmpRoot, DS);
}
define('DOCROOT', $tmp.DS.'docroot'.DS);
define('APPPATH', $tmp.DS.'app'.DS);
define('PKGPATH', $tmp.DS.'pkg'.DS);
define('COREPATH', $tmp.DS.'core'.DS);

Tests\Support\FuelTestEnvironment::bootstrapDirectories();

require TEST_ROOT.'/bootstrap.php';

\Autoloader::add_classes(array(
	'Fuel\\Tasks\\Fromdb' => TEST_ROOT.'/tasks/fromdb.php',
));

spl_autoload_register(function ($class) {
	$prefix = 'Tests\\Oil\\';
	if (strpos($class, $prefix) !== 0)
	{
		return;
	}
	$relative = substr($class, strlen($prefix));
	$path = __DIR__.'/Oil/'.str_replace('\\', '/', $relative).'.php';
	if (is_file($path))
	{
		require $path;
	}
});
