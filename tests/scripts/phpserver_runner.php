<?php

require dirname(__DIR__).'/bootstrap.php';

$server = json_decode($argv[1] ?? getenv('OIL_SERVER'), true);
foreach ($server as $k => $v)
{
	$_SERVER[$k] = $v;
}
$result = include TEST_ROOT.'/phpserver.php';
echo $result === false ? 'false' : 'true';
