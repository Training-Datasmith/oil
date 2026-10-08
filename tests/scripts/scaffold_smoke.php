<?php

error_reporting(-1);
require dirname(__DIR__).'/bootstrap.php';

\Oil\Generate_Scaffold::_init();
is_dir(APPPATH.'migrations') or mkdir(APPPATH.'migrations', 0755, true);
try
{
	\Oil\Generate_Scaffold::forge(array('post', 'title:string[40]'), 'orm');
	echo "OK\n";
}
catch (Throwable $e)
{
	echo "ERR: ".$e->getMessage()."\n";
}
