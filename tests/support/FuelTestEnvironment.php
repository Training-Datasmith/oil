<?php

namespace Tests\Support;

class FuelTestEnvironment
{
	public static $namespace_paths = array();

	public static function bootstrapDirectories()
	{
		foreach (array(DOCROOT, APPPATH, PKGPATH, COREPATH) as $dir)
		{
			is_dir($dir) or mkdir($dir, 0755, true);
		}
	}

	public static function resetWorkspace()
	{
		foreach (array(APPPATH, PKGPATH, COREPATH) as $root)
		{
			static::emptyDir($root);
			is_dir($root) or mkdir($root, 0755, true);
		}
		static::emptyDir(DOCROOT);
		is_dir(DOCROOT) or mkdir(DOCROOT, 0755, true);
	}

	protected static function emptyDir($dir)
	{
		if ( ! is_dir($dir))
		{
			return;
		}
		$it = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $file)
		{
			$file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
		}
	}

	public static function resetAll()
	{
		static::resetWorkspace();
		static::$namespace_paths = array();
		\Cli::reset();
		\Finder::reset();
		\Module::reset();
		\DB::reset();
		\DBUtil::reset();
		\File::$update_should_throw = null;
		\Config::reset();
		\Fuel::$env = 'development';
		\Oil\Generate::$create_files = array();
		\Oil\Generate::$create_folders = array();
		\Oil\Generate::$scaffolding = false;
		\Config::set('db.active', 'default');
		\Config::set('db.default.table_prefix', '');
		\Config::set('controller_prefix', 'Controller_');
		\Config::set('module_paths', array(APPPATH.'modules'.DS));
		\Config::set('package_paths', array(PKGPATH));
		\Config::set('migrations.table', 'migration');
		\Config::set('package', array('sources' => array()));
	}
}
