<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\FS;


/**
 * @covers \spaf\simputils\FS
 * @covers \spaf\simputils\models\File
 *
 */
class FilesManagementTest extends TestCase {

	public function testCreateAndDelete() {
		$location = '/tmp/simputils/tests';
		$file = "{$location}/__just-a-test-file.txt";
		$dir = "{$location}/__just-a-test-dir";

		FS::mkFile($file, 'My test content');
		FS::mkFile($file);
		$this->assertFileExists($file, 'File was created');

		FS::mkDir($dir);
		$this->assertDirectoryExists($dir, 'Directory was created');

		FS::rmFile($file);
		$this->assertFileDoesNotExist($file, 'File was deleted');

		FS::rmDir($dir, true);
		$this->assertDirectoryDoesNotExist($dir, 'Directory was deleted');
	}

	public function testFilesTransparentActionsCases() {
		$res = FS::rmFile(null);
		$this->assertNull($res, 'Transparent usage of rmFile() for null');

		$location = '/tmp/simputils/tests';
		$file = "{$location}/non-existing-file-(hopefully).txtXTX";
		// Make sure file does not exist
		FS::rmFile($file);

		$res = FS::rmFile($file);
		$this->assertTrue($res, 'Transparent usage of rmFile() for non-existing file');

//		$res = PHP::getFileContent(null);
//		$this->assertFalse($res, 'If no content, false should be returned');
	}

	public function testSorting() {
		$location = '/tmp/simputils/tests';
		$res = FS::lsFiles($location, true, true);
		$this->assertIsArray($res, 'Result should be an array');
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testRmDirException() {
		$location = '/tmp/simputils/tests';
		$file = "{$location}/temp-file-to-test-rm-dir-exception.txt";
		FS::mkFile($file, recursively: true);

		$this->expectException(Exception::class);

		FS::rmDir($file);
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testRmFileDirException() {
		$location = '/tmp/simputils/tests';
		$dir = "{$location}/dir-strict-cannot-let-to-delete";
		FS::mkDir($dir, recursively: true);

		$this->expectException(Exception::class);

		// Exception here because strict file deletion does not allow dir deletion
		FS::rmFile($dir, strict: true);
	}
}
