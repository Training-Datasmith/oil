<?php

namespace Tests\Oil;

use Oil\Package;
use Tests\Support\OilTestCase;

class PackageTest extends OilTestCase
{
	public function testHelp()
	{
		Package::help();
		$this->assertStringContainsString('php oil package install', \Cli::capturedOutput());
		$this->assertStringContainsString('--direct', \Cli::capturedOutput());
	}

	public function testInstallNullShowsHelp()
	{
		Package::install(null);
		$this->assertStringContainsString('php oil package install', \Cli::capturedOutput());
	}

	public function testInstallAlreadyInstalled()
	{
		mkdir(PKGPATH.'auth', 0755, true);
		$this->expectException(\Oil\Exception::class);
		$this->expectExceptionMessage('already installed');
		Package::install('auth');
	}

	public function testUninstallProtected()
	{
		$this->expectException(\Oil\Exception::class);
		$this->expectExceptionMessage('cannot be uninstalled');
		Package::uninstall('oil');
	}

	public function testUninstallMissing()
	{
		$this->expectException(\Oil\Exception::class);
		$this->expectExceptionMessage('is not installed');
		Package::uninstall('missing');
	}

	public function testUninstallDeletesTree()
	{
		$dir = PKGPATH.'demo';
		mkdir($dir, 0755, true);
		file_put_contents($dir.DS.'keep.txt', 'x');
		Package::uninstall('demo');
		$this->assertFalse(is_dir($dir));
		$this->assertStringContainsString('was uninstalled', \Cli::capturedOutput());
	}

	public function testInstallUnknownPackage()
	{
		$server = $this->startZipServer('');
		\Config::set('package', array('sources' => array($server['host'].':'.$server['port'])));
		$this->expectException(\Oil\Exception::class);
		$this->expectExceptionMessage('Could not find package "missing"');
		Package::install('missing');
		proc_terminate($server['proc']);
	}

	public function testInstallDirectZip()
	{
		$zipBody = $this->buildDemoZip();
		$zipFile = sys_get_temp_dir().DS.'oil-demo-'.uniqid().'.zip';
		file_put_contents($zipFile, $zipBody);
		$server = $this->startZipServer($zipFile);
		$script = TEST_ROOT.'/tests/scripts/package_install_direct.php';
		$cmd = PHP_BINARY.' '.escapeshellarg($script).' '.$server['host'].' '.$server['port'];
		ob_start();
		exec($cmd, $out, $code);
		ob_end_clean();
		proc_terminate($server['proc']);
		$this->assertSame(0, $code);
		$this->assertFileExists(PKGPATH.'demopkg'.DS.'README.txt');
		$this->assertStringContainsString('hello-oil', file_get_contents(PKGPATH.'demopkg'.DS.'README.txt'));
	}

	protected function buildDemoZip()
	{
		$tmp = sys_get_temp_dir().DS.'oil-zip-'.uniqid();
		mkdir($tmp);
		mkdir($tmp.DS.'DemopkgAbc');
		file_put_contents($tmp.DS.'DemopkgAbc'.DS.'README.txt', 'hello-oil');
		$zip = $tmp.'.zip';
		if (class_exists('ZipArchive'))
		{
			$z = new \ZipArchive();
			$z->open($zip, \ZipArchive::CREATE);
			$z->addFile($tmp.DS.'DemopkgAbc'.DS.'README.txt', 'DemopkgAbc/README.txt');
			$z->close();
		}
		else
		{
			exec('cd '.escapeshellarg($tmp).' && zip -qr '.escapeshellarg($zip).' DemopkgAbc 2>/dev/null');
		}
		if ( ! is_file($zip))
		{
			$this->markTestSkipped('zip tooling is not available');
		}
		return file_get_contents($zip);
	}

	protected function startZipServer($zipFile)
	{
		$script = TEST_ROOT.'/tests/scripts/builtin_server_router.php';
		$sock = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
		$addr = stream_socket_get_name($sock, false);
		list($host, $port) = explode(':', $addr);
		fclose($sock);
		$cmd = PHP_BINARY.' -S '.$host.':'.$port.' -t /tmp '.escapeshellarg($script);
		$descriptor = array(0 => array('pipe', 'r'), 1 => array('file', '/tmp/oil-server.log', 'a'), 2 => array('file', '/tmp/oil-server.log', 'a'));
		$env = array('OIL_ZIP_FILE' => $zipFile);
		$proc = proc_open($cmd, $descriptor, $pipes, TEST_ROOT, $env);
		for ($i = 0; $i < 50; $i++)
		{
			$fp = @fsockopen($host, (int) $port, $errno, $errstr, 0.1);
			if ($fp)
			{
				fclose($fp);
				break;
			}
		}
		return array('host' => $host, 'port' => $port, 'proc' => $proc);
	}
}
