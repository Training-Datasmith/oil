<?php

error_reporting(-1);
require dirname(__DIR__).'/bootstrap.php';
\Oil\Generate_Scaffold::_init();
is_dir(APPPATH.'migrations') or mkdir(APPPATH.'migrations', 0755, true);
ob_start();
\Oil\Generate_Scaffold::forge(array('post', 'title:string[40]'), 'orm');
$junk = ob_get_clean();
echo "junk_len=".strlen($junk)."\n";
echo file_exists(APPPATH.'classes/model/post.php') ? "model_ok\n" : "model_missing\n";
