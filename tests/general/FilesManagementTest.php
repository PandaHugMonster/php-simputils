<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\PHP;


/**
 * @covers \spaf\simputils\PHP::mkFile
 * @covers \spaf\simputils\PHP::mkDir
 * @covers \spaf\simputils\PHP::rmFile
 * @covers \spaf\simputils\PHP::rmDir
 * @covers \spaf\simputils\PHP::listFiles
 * @covers \spaf\simputils\PHP::getFileContent
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

	public function testFilesTransparentActionsCases() {
		$res = PHP::rmFile(null);
		$this->assertNull($res, 'Transparent usage of rmFile() for null');

		$location = '/tmp/simputils/tests';
		$file = "{$location}/non-existing-file-(hopefully).txtXTX";
		// Make sure file does not exist
		PHP::rmFile($file);

		$res = PHP::rmFile($file);
		$this->assertTrue($res, 'Transparent usage of rmFile() for non-existing file');

		$res = PHP::getFileContent(null);
		$this->assertFalse($res, 'If no content, false should be returned');
	}

	public function testSorting() {
		$location = '/tmp/simputils/tests';
		$res = PHP::listFiles($location, true, true);
		$this->assertIsArray($res, 'Result should be an array');
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testRmDirException() {
		$location = '/tmp/simputils/tests';
		$file = "{$location}/temp-file-to-test-rm-dir-exception.txt";
		PHP::mkFile($file, recursively: true);

		$this->expectException(Exception::class);

		PHP::rmDir($file);
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testRmFileDirException() {
		$location = '/tmp/simputils/tests';
		$dir = "{$location}/dir-strict-cannot-let-to-delete";
		PHP::mkDir($dir, recursively: true);

		$this->expectException(Exception::class);

		// Exception here because strict file deletion does not allow dir deletion
		PHP::rmFile($dir, strict: true);
	}
}
