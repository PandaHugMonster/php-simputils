<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\PHP;


/**
 * @covers \spaf\simputils\PHP::mkFile
 * @covers \spaf\simputils\PHP::mkDir
 * @covers \spaf\simputils\PHP::rmFile
 * @covers \spaf\simputils\PHP::rmDir
 */
class FilesManagementTest extends TestCase {

	public function testCreateAndDelete() {
		$location = '/tmp/simputils/tests';
		$file = "{$location}/__just-a-test-file.txt";
		$dir = "{$location}/__just-a-test-dir";

		PHP::mkFile($file, 'My test content');
		PHP::mkFile($file);
		$this->assertFileExists($file, 'File was created');

		PHP::mkDir($dir);
		$this->assertDirectoryExists($dir, 'Directory was created');

		PHP::rmFile($file);
		$this->assertFileDoesNotExist($file, 'File was deleted');

		PHP::rmDir($dir, true);
		$this->assertDirectoryDoesNotExist($dir, 'Directory was deleted');

	}

}