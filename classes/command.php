<?php

declare(strict_types=1);
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

namespace Oil;

/**
 * Oil\Cli Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 */
class Command
{
    public static function init($args): void
    {
        \Config::load('oil', true);

        // Remove flag options from the main argument list
        $args = self::_clear_args($args);

        try {
            if (! isset($args[1])) {
                if (\Cli::option('v', \Cli::option('version'))) {
                    \Cli::write('Fuel: '.\Fuel::VERSION.' running in "'.\Fuel::$env.'" mode');
                    return;
                }

                static::help();
                return;
            }

            switch ($args[1]) {
                case 'g':
                case 'generate':

                    $action = $args[2] ?? 'help';

                    $subfolder = 'orm';
                    if (str_contains($action, '/')) {
                        [$action, $subfolder] = explode('/', $action);
                    }

                    match ($action) {
                        'config', 'controller', 'model', 'module', 'migration', 'task', 'package' => call_user_func('Oil\Generate::'.$action, array_slice($args, 3)),
                        'views' => call_user_func(Oil\Generate::views(...), array_slice($args, 3), $subfolder),
                        'admin' => call_user_func(Oil\Generate_Admin::forge(...), array_slice($args, 3), $subfolder),
                        'scaffold' => call_user_func(Oil\Generate_Scaffold::forge(...), array_slice($args, 3), $subfolder),
                        default => Generate::help(),
                    };

                    break;

                case 'c':
                case 'console':

                    if (isset($args[2]) and $args[2] == 'help') {
                        Console::help();
                    } else {
                        new Console();
                    }

                    break;

                case 'p':
                case 'package':

                    $action = $args[2] ?? 'help';

                    match ($action) {
                        'install', 'uninstall' => call_fuel_func_array('Oil\Package::'.$action, array_slice($args, 3)),
                        default => Package::help(),
                    };

                    break;

                case 'r':
                case 'refine':

                    $task = $args[2] ?? null;
                    call_user_func(Oil\Refine::run(...), $task, array_slice($args, 3));

                    break;

                case 't':
                case 'test':

                    if (isset($args[2]) and $args[2] == 'help') {
                        $output = <<<HELP

Usage:
  php oil [t|test]

Runtime options:
  --file=<file>              # Run a test on a specific file only.
  --group=<group>            # Only runs tests from the specified group(s).
  --exclude-group=<group>    # Exclude tests from the specified group(s).
  --testsuite=<testsuite>    # Only runs tests from the specified testsuite(s).
  --coverage-clover=<file>   # Generate code coverage report in Clover XML format.
  --coverage-html=<dir>      # Generate code coverage report in HTML format.
  --coverage-php=<file>      # Serialize PHP_CodeCoverage object to file.
  --coverage-text=<file>     # Generate code coverage report in text format.
  --log-junit=<file>         # Generate report of test execution in JUnit XML format to file.
  --debug                    # Display debugging information during test execution.

Description:
  Run phpunit on all or a subset of tests defined for the current application.

Examples:
  php oil test

Documentation:
  https://fuelphp.com/docs/packages/oil/test.html
HELP;
                        \Cli::write($output);
                    } else {
                        $phpunit_command = \Config::get('oil.phpunit.binary_path', 'phpunit');

                        // Check if we might be using the phar library
                        $is_phar = false;
                        foreach (explode(PATH_SEPARATOR, getenv('PATH')) as $path) {
                            if (is_file($path.DS.$phpunit_command)) {
                                $handle = fopen($path.DS.$phpunit_command, 'r');
                                if ($handle !== false) {
                                    $is_phar = fread($handle, 18) == '#!/usr/bin/env php';
                                    fclose($handle);
                                }
                                if ($is_phar) {
                                    break;
                                }
                            }
                        }

                        // Suppressing this because if the file does not exist... well thats a bad thing and we can't really check
                        // I know that supressing errors is bad, but if you're going to complain: shut up. - Phil
                        $phpunit_autoload_path = \Config::get('oil.phpunit.autoload_path', 'PHPUnit/Autoload.php');
                        @include_once $phpunit_autoload_path;

                        // Attempt to load PHPUnit.  If it fails, we are done.
                        if (! $is_phar and ! class_exists('PHPUnit\Framework\TestCase') and ! class_exists('PHPUnit_Framework_TestCase')) {
                            throw new Exception('PHPUnit does not appear to be installed.'.PHP_EOL.PHP_EOL."\tPlease visit https://phpunit.de and install.");
                        }

                        // Check for a custom phpunit config, but default to the one from core
                        if (is_file(APPPATH.'phpunit.xml')) {
                            $phpunit_config = APPPATH.'phpunit.xml';
                        } else {
                            $phpunit_config = COREPATH.'phpunit.xml';
                        }

                        // CD to the root of Fuel and call up phpunit with the path to our config
                        $command = 'cd '.DOCROOT.'; '.$phpunit_command.' -c "'.$phpunit_config.'"';

                        // Respect the group options
                        \Cli::option('group') and $command .= ' --group '.escapeshellarg(\Cli::option('group'));
                        \Cli::option('exclude-group') and $command .= ' --exclude-group '.escapeshellarg(\Cli::option('exclude-group'));

                        // Respect the testsuite options
                        \Cli::option('testsuite') and $command .= ' --testsuite '.escapeshellarg(\Cli::option('testsuite'));

                        // Respect the debug options
                        \Cli::option('debug') and $command .= ' --debug';

                        // Respect the coverage-html option
                        \Cli::option('coverage-html') and $command .= ' --coverage-html '.escapeshellarg(\Cli::option('coverage-html'));
                        \Cli::option('coverage-clover') and $command .= ' --coverage-clover '.escapeshellarg(\Cli::option('coverage-clover'));
                        \Cli::option('coverage-text') and $command .= ' --coverage-text='.escapeshellarg(\Cli::option('coverage-text'));
                        \Cli::option('coverage-php') and $command .= ' --coverage-php '.escapeshellarg(\Cli::option('coverage-php'));
                        \Cli::option('log-junit') and $command .= ' --log-junit '.escapeshellarg(\Cli::option('log-junit'));
                        \Cli::option('file') and $command .= ' '.escapeshellarg(\Cli::option('file'));

                        \Cli::write('Tests Running...This may take a few moments.', 'green');

                        $return_code = 0;
                        foreach (explode(';', $command) as $c) {
                            passthru($c, $return_code_task);
                            // Return failure if any subtask fails
                            $return_code |= $return_code_task;
                        }
                        exit($return_code);
                    }

                    break;

                case 's':
                case 'server':

                    if (isset($args[2]) and $args[2] == 'help') {
                        $output = <<<HELP

Usage:
  php oil [s|server]

Runtime options:
  --php=<file>               # The full pathname of your PHP-CLI binary if it's not in the path.
  --port=<port>              # TCP port number the webserver should listen too. Defaults to 8000.
  --host=<host>              # Hostname the webserver should run at. Defaults to "localhost".
  --docroot=<dir>            # Your FuelPHP docroot. Defaults to "public".
  --router=<file>            # PHP router script. Defaults to "fuel/packages/oil/phpserver.php".

Description:
  Starts a local webserver to run your FuelPHP application, using PHP 5.4+ internal webserver.

Examples:
  php oil server -p=8080

Documentation:
  https://fuelphp.com/docs/packages/oil/server.html
HELP;
                        \Cli::write($output);
                    } else {
                        $php = \Cli::option('php', 'php');
                        $port = \Cli::option('p', \Cli::option('port', '8000'));
                        $host = \Cli::option('h', \Cli::option('host', 'localhost'));
                        $docroot = \Cli::option('d', \Cli::option('docroot', 'public'));
                        $router = \Cli::option('r', \Cli::option('router', __DIR__.DS.'..'.DS.'phpserver.php'));

                        \Cli::write("Listening on http://$host:$port");
                        \Cli::write("Document root is $docroot");
                        \Cli::write('Press Ctrl-C to quit.');
                        passthru(escapeshellarg($php).' -S '.escapeshellarg($host.':'.$port).' -t '.escapeshellarg($docroot).' '.escapeshellarg($router));
                    }
                    break;

                case 'create':
                    \Cli::write('You can not use "oil create", a valid FuelPHP installation already exists in this directory', 'red');
                    break;

                default:

                    static::help();
            }
        } catch (\Exception $e) {
            static::print_exception($e);
            exit(1);
        }
    }

    protected static function print_exception(\Exception $ex)
    {
        // create the error message, log and display it
        $msg = $ex->getCode().' - '.$ex->getMessage().' in '.$ex->getFile().' on line '.$ex->getLine();
        logger(\Fuel::L_ERROR, $msg);
        \Cli::error('Uncaught exception '.$ex::class.': '.$msg);

        // print a trace if not in production, don't want to spoil external logging
        if (\Fuel::$env != \Fuel::PRODUCTION) {
            \Cli::error('Callstack: ');
            \Cli::error($ex->getTraceAsString());
        }
        \Cli::beep();

        // print any previous exception(s) too...
        if (($previous = $ex->getPrevious()) != null) {
            \Cli::error('');
            \Cli::error('Previous exception: ');
            static::print_exception($previous);
        }
    }

    public static function help(): void
    {
        echo <<<HELP

Usage:
  php oil [console|generate|package|refine|help|server|test]

Runtime options:
  -f, [--force]    # Overwrite files that already exist
  -s, [--skip]     # Skip files that already exist
  -q, [--quiet]    # Supress status output
  -t, [--speak]    # Speak errors in a robot voice

Description:
  The 'oil' command can be used in several ways to facilitate quick development, help with
  testing your application and for running Tasks.

Environment:
  If you want to specify a specific environment oil has to run in, overload the environment
  variable on the commandline: FUEL_ENV=staging php oil <commands>

More information:
  You can pass the parameter "help" to each of the defined command to get information
  about that specific command: php oil package help

Documentation:
  https://docs.fuelphp.com/packages/oil/intro.html

HELP;

    }

    protected static function _clear_args(array $actions = []): array
    {
        foreach ($actions as $key => $action) {
            if (str_starts_with((string) $action, '-')) {
                unset($actions[$key]);
            }

            // get rid of any junk added by Powershell on Windows...
            isset($actions[$key]) and $actions[$key] = trim($actions[$key]);
        }

        return $actions;
    }
}

/* End of file oil/classes/command.php */
