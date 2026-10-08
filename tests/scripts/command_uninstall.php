<?php

error_reporting(-1);
require dirname(__DIR__).'/bootstrap.php';

\Fuel::$env = $argv[1] ?? 'development';

\Oil\Command::init(array('oil', 'package', 'uninstall', 'oil'));
