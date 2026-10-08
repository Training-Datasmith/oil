<?php

namespace {

class Autoloader
{
	protected static $classes = array();

	public static function add_classes(array $classes)
	{
		static::$classes = array_merge(static::$classes, $classes);
	}

	public static function namespace_path($namespace)
	{
		return \Tests\Support\FuelTestEnvironment::$namespace_paths[$namespace] ?? false;
	}

	public static function load($class)
	{
		if (isset(static::$classes[$class]))
		{
			require static::$classes[$class];
		}
	}
}

spl_autoload_register(array('Autoloader', 'load'));

class Fuel
{
	const VERSION = '1.9-test';
	const PRODUCTION = 'production';
	const L_ERROR = 3;
	const L_DEBUG = 7;

	public static $env = 'development';

	public static function load($path)
	{
		return include $path;
	}
}

class Config
{
	protected static $data = array();

	public static function reset()
	{
		static::$data = array();
	}

	public static function load($file, $group = false)
	{
		$key = $group === true ? $file : $file;
		if ( ! isset(static::$data[$key]))
		{
			$path = OIL_ROOT.'/config/'.$file.'.php';
			static::$data[$key] = is_file($path) ? include $path : array();
		}
		return static::$data[$key];
	}

	public static function get($key, $default = null)
	{
		$parts = explode('.', $key);
		$root = array_shift($parts);
		if ( ! isset(static::$data[$root]))
		{
			static::load($root, true);
		}
		$val = static::$data[$root] ?? array();
		foreach ($parts as $part)
		{
			if ( ! is_array($val) || ! array_key_exists($part, $val))
			{
				return $default;
			}
			$val = $val[$part];
		}
		return $val;
	}

	public static function set($key, $value)
	{
		$parts = explode('.', $key);
		$root = array_shift($parts);
		if ( ! isset(static::$data[$root]))
		{
			static::$data[$root] = array();
		}
		$ref = &static::$data[$root];
		foreach ($parts as $part)
		{
			if ( ! isset($ref[$part]) || ! is_array($ref[$part]))
			{
				$ref[$part] = array();
			}
			$ref = &$ref[$part];
		}
		$ref = $value;
	}
}

class Cli
{
	public static $readline_support = false;

	protected static $options = array();
	protected static $writes = array();
	protected static $errors = array();
	protected static $input_queue = array();
	protected static $prompt_queue = array();

	public static function reset()
	{
		static::$options = array();
		static::$writes = array();
		static::$errors = array();
		static::$input_queue = array();
		static::$prompt_queue = array();
	}

	public static function option($key, $default = null)
	{
		return array_key_exists($key, static::$options) ? static::$options[$key] : $default;
	}

	public static function set_option($key, $value)
	{
		static::$options[$key] = $value;
	}

	public static function write($text, $foreground = null, $background = null)
	{
		$line = is_array($text) ? implode("\n", $text) : (string) $text;
		static::$writes[] = $line;
		if (getenv('OIL_TEST_ECHO_CLI'))
		{
			fwrite(STDERR, $line.PHP_EOL);
		}
	}

	public static function error($text)
	{
		static::$errors[] = (string) $text;
	}

	public static function beep()
	{
	}

	public static function color($text, $foreground = null, $background = null)
	{
		return (string) $text;
	}

	public static function input($prompt = '')
	{
		if (empty(static::$input_queue))
		{
			return '';
		}
		return array_shift(static::$input_queue);
	}

	public static function queue_input($line)
	{
		static::$input_queue[] = $line;
	}

	public static function prompt($text, $options = array())
	{
		if ( ! empty(static::$prompt_queue))
		{
			return array_shift(static::$prompt_queue);
		}
		return reset($options);
	}

	public static function queue_prompt($value)
	{
		static::$prompt_queue[] = $value;
	}

	public static function capturedOutput()
	{
		return implode("\n", static::$writes);
	}

	public static function capturedErrors()
	{
		return implode("\n", static::$errors);
	}
}

class File
{
	public static $update_should_throw = null;

	public static function update($dir, $file, $contents)
	{
		if (static::$update_should_throw)
		{
			$ex = static::$update_should_throw;
			static::$update_should_throw = null;
			throw $ex;
		}
		$path = rtrim($dir, DS).DS.$file;
		$parent = dirname($path);
		is_dir($parent) or mkdir($parent, 0755, true);
		file_put_contents($path, $contents);
	}

	public static function delete_dir($path)
	{
		if (static::$update_should_throw instanceof \Exception)
		{
			throw static::$update_should_throw;
		}
		if ( ! is_dir($path))
		{
			return;
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $file)
		{
			$file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
		}
		rmdir($path);
	}
}

class Finder
{
	protected static $instance;
	protected static $search_paths = array();
	protected static $search_results = array();
	protected static $list_files = array();

	public static function instance()
	{
		static::$instance or static::$instance = new static();
		return static::$instance;
	}

	public static function reset()
	{
		static::$instance = null;
		static::$search_paths = array();
		static::$search_results = array();
		static::$list_files = array();
	}

	public static function search($dir, $file, $ext = '.php', $multiple = false)
	{
		$key = $dir.'/'.$file;
		if (isset(static::$search_results[$key]))
		{
			return static::$search_results[$key];
		}
		return $multiple ? array() : false;
	}

	public static function set_search($dir, $file, $paths)
	{
		static::$search_results[$dir.'/'.$file] = $paths;
	}

	public static function set_list_files($dir, $files)
	{
		static::$list_files[$dir] = $files;
	}

	public function add_path($path, $priority = 0)
	{
		static::$search_paths[] = $path;
	}

	public static function forge($path)
	{
		$finder = new static();
		$finder->module_path = rtrim($path, DS).DS;
		return $finder;
	}

	public $module_path;

	public function list_files($dir)
	{
		if ($this->module_path)
		{
			$key = 'module:'.$this->module_path.':'.$dir;
			return static::$list_files[$key] ?? array();
		}
		$files = static::$list_files[$dir] ?? array();
		$out = array();
		foreach ($files as $f)
		{
			$out[] = is_array($f) ? $f['path'] : $f;
		}
		return $out;
	}

	public static function set_module_list_files($module_path, $dir, $files)
	{
		static::$list_files['module:'.rtrim($module_path, DS).DS.':'.$dir] = $files;
	}
}

class Module
{
	protected static $exists = array();
	protected static $loaded = array();

	public static function reset()
	{
		static::$exists = array();
		static::$loaded = array();
	}

	public static function exists($name)
	{
		return static::$exists[$name] ?? false;
	}

	public static function set_exists($name, $path)
	{
		static::$exists[$name] = rtrim($path, DS).DS;
	}

	public static function load($name)
	{
		if ( ! static::exists($name))
		{
			throw new FuelException('Module not found');
		}
		static::$loaded[$name] = static::$exists[$name];
	}

	public static function loaded()
	{
		return static::$loaded;
	}

	public static function set_loaded($name, $path)
	{
		static::$loaded[$name] = rtrim($path, DS).DS;
	}
}

class Str
{
	public static function lower($value)
	{
		return mb_strtolower((string) $value);
	}

	public static function random()
	{
		return 'oil-random-'.mt_rand();
	}

	public static function ends_with($haystack, $needle)
	{
		$needle = (string) $needle;
		if ($needle === '')
		{
			return true;
		}
		return substr((string) $haystack, -strlen($needle)) === $needle;
	}

	public static function ucwords($str)
	{
		return ucwords((string) $str);
	}

	public static function increment($str, $first = 1)
	{
		if (preg_match('/(.+)_([0-9]+)$/', $str, $m))
		{
			return $m[1].'_'.((int) $m[2] + 1);
		}
		return $str.'_'.$first;
	}
}

class Inflector
{
	public static function humanize($str)
	{
		return ucwords(str_replace('_', ' ', $str));
	}

	public static function classify($name, $singularize = true)
	{
		$name = str_replace(array('/', '-'), '_', $name);
		if ($singularize)
		{
			$name = static::singularize($name);
		}
		return str_replace(' ', '_', ucwords(str_replace('_', ' ', $name)));
	}

	public static function singularize($word)
	{
		if (substr($word, -3) === 'ies')
		{
			return substr($word, 0, -3).'y';
		}
		if (substr($word, -1) === 's' && substr($word, -2) !== 'ss')
		{
			return substr($word, 0, -1);
		}
		return $word;
	}

	public static function pluralize($word)
	{
		if (substr($word, -1) === 'y')
		{
			return substr($word, 0, -1).'ies';
		}
		if (substr($word, -1) !== 's')
		{
			return $word.'s';
		}
		return $word;
	}

	public static function tableize($name)
	{
		return strtolower(static::pluralize(str_replace('_', ' ', $name)));
	}

	public static function underscore($name)
	{
		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
	}
}

class View
{
	protected static $app_views = array();

	public static function set_app_view($name, $path)
	{
		static::$app_views[$name] = $path;
	}

	public static function forge($name, $data = array())
	{
		return new \Tests\Support\LazyView($name, $data);
	}
}

class DB
{
	public static $list_tables = array();
	public static $list_columns = array();
	public static $list_indexes = array();
	public static $table_prefix = '';
	public static $list_tables_exception = null;

	public static function reset()
	{
		static::$list_tables = array();
		static::$list_columns = array();
		static::$list_indexes = array();
		static::$table_prefix = '';
		static::$list_tables_exception = null;
	}

	public static function list_tables($db = null, $name = null)
	{
		if (static::$list_tables_exception)
		{
			throw static::$list_tables_exception;
		}
		return static::$list_tables;
	}

	public static function list_columns($table, $column = null, $db = null)
	{
		$key = $table.':'.($column ?? '*');
		if (isset(static::$list_columns[$key]))
		{
			return static::$list_columns[$key];
		}
		if (isset(static::$list_columns[$table]))
		{
			return static::$list_columns[$table];
		}
		return array();
	}

	public static function list_indexes($table, $db = null, $name = null)
	{
		return static::$list_indexes[$table] ?? array();
	}

	public static function table_prefix()
	{
		return static::$table_prefix;
	}

	public static function quote_identifier($col)
	{
		return '`'.$col.'`';
	}
}

class DBUtil
{
	public static $table_exists = array();

	public static function reset()
	{
		static::$table_exists = array();
	}

	public static function table_exists($table)
	{
		return static::$table_exists[$table] ?? false;
	}
}

class Unzip
{
	public function extract($zip_file, $dest)
	{
		$zip = new ZipArchive();
		$zip->open($zip_file);
		$zip->extractTo($dest);
		$files = array();
		for ($i = 0; $i < $zip->numFiles; $i++)
		{
			$files[] = $dest.DS.$zip->getNameIndex($i);
		}
		$zip->close();
		return $files;
	}
}

class Package
{
	public static function exists($name)
	{
		if ($name === 'oil')
		{
			return OIL_ROOT.DS;
		}
		return false;
	}
}

class FuelException extends \Exception {}
class InvalidPathException extends \Exception {}
class FileAccessException extends \Exception {}

function logger($level, $msg) {}

function call_fuel_func_array($callback, $args)
{
	return call_user_func_array($callback, $args);
}

}
