<?php

namespace Tests\Oil;

use Oil\Generate;
use Oil\Generate_Migration_Actions;
use Tests\Support\OilTestCase;

class GenerateMigrationTest extends OilTestCase
{
	public function testFirstMigrationNumberWhenDirMissing()
	{
		$this->assertFalse(is_dir(APPPATH.'migrations'));
		Generate::migration(array('create_posts', 'title:string'));
		$this->assertFileExists(APPPATH.'migrations'.DS.'001_create_posts.php');
	}

	public function testCreatePostsShape()
	{
		is_dir(APPPATH.'migrations') or mkdir(APPPATH.'migrations', 0755, true);
		Generate::migration(array('create_posts', 'title:string[50]'));
		$files = glob(APPPATH.'migrations'.DS.'*_create_posts.php');
		$this->assertNotEmpty($files);
		$content = file_get_contents($files[0]);
		$this->assertStringContainsString('create_table', $content);
		$this->assertStringContainsString("'posts'", $content);
		$this->assertStringContainsString("'constraint' => 50", $content);
		$this->assertStringContainsString('drop_table', $content);
	}

	public function testAddAndDeleteSwap()
	{
		is_dir(APPPATH.'migrations') or mkdir(APPPATH.'migrations', 0755, true);
		\Cli::set_option('no-standardisation', true);
		Generate::migration(array('add_bio_to_posts', 'bio:text'));
		$add = file_get_contents(glob(APPPATH.'migrations'.DS.'*_add_bio_to_posts.php')[0]);
		$this->assertStringContainsString("add_fields('posts'", $add);
		Generate::$create_files = array();
		Generate::migration(array('delete_bio_from_posts', 'bio:text'));
		$del = file_get_contents(glob(APPPATH.'migrations'.DS.'*_delete_bio_from_posts.php')[0]);
		$this->assertStringContainsString("drop_fields('posts'", $del);
	}

	public function testEmptyMagicNameThrows()
	{
		$this->expectException(\Exception::class);
		Generate::migration(array('not_an_action'));
	}

	public function testBadCreateSubjectCount()
	{
		$this->expectException(\FuelException::class);
		Generate_Migration_Actions::create(array(false), array());
	}
}
