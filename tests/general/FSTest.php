<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\FS;
use spaf\simputils\PHP;


/**
 * @covers \spaf\simputils\FS
 * @covers \spaf\simputils\models\File
 *
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\models\files\apps\TextProcessor
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__set
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__isset
 * @uses \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\models\files\apps\CsvProcessor
 */
class FSTest extends TestCase {

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

	function testRmFileObject() {
		$file = PHP::file('/tmp/dot-dot-dot-test-file-blabla-bla.txt');
		$file->content = " --- FILE CONTENT --- ";

		$this->assertFileExists($file->name_full);
		FS::rmFile($file);
		$this->assertFileDoesNotExist($file->name_full);
	}

	function testMimeTypeCheck() {
		$file = PHP::file('/tmp/dot-dot-dot-test-file-blabla-bla.txt');
		$this->assertEquals('application/x-empty', $file->mime_type);
		$file->move(ext: 'csv');

		$file->content = [[1, 2, 3]];

		$mime = FS::getFileMimeType($file);
		$this->assertEquals('text/csv', $mime);
	}
}
