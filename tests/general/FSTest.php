<?php /** @noinspection ALL */

use PHPUnit\Framework\TestCase;
use spaf\simputils\components\filters\DirExtFilter;
use spaf\simputils\components\filters\OnlyDirsFilter;
use spaf\simputils\components\filters\OnlyFilesFilter;
use spaf\simputils\FS;
use spaf\simputils\models\Box;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use spaf\simputils\PHP;
use spaf\simputils\Str;


/**
 * @covers \spaf\simputils\FS
 * @covers \spaf\simputils\models\File
 * @covers \spaf\simputils\models\Dir
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
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsPrepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsGetValidator
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
		$file = FS::file('/tmp/dot-dot-dot-test-file-blabla-bla.txt');
		$file->content = " --- FILE CONTENT --- ";

		$this->assertFileExists($file->name_full);
		FS::rmFile($file);
		$this->assertFileDoesNotExist($file->name_full);
	}

	/**
	 * @covers \spaf\simputils\FS::getFileMimeType
	 *
	 * @runInSeparateProcess
	 * @return void
	 * @throws \Exception
	 */
	function testOther() {
		$path = FS::path('path1', 'path2', 'path3');
		$this->assertIsString($path);
		// TODO Windows should be supported too?
		$this->assertEquals('path1/path2/path3', $path);


		$orig_dir = new Dir('/tmp');

		$this->assertEquals($orig_dir, FS::dir($orig_dir));
		$this->assertInstanceOf(Dir::class, FS::dir('/tmp/new-temp-folder'));

		$orig = new File('/tmp/my-text-file.txt');

		$this->assertEquals($orig, FS::file($orig));
		$this->assertInstanceOf(File::class, FS::file('/tmp/new-temp-file.csv'));
		$this->assertEquals($orig->mime_type, FS::getFileMimeType($orig));

		$file = FS::file('/tmp/test1/test2/test3/what/is-that.question-mark');

		// $file->format('') what/is-that.question-mark
		// $file->format('') what/is-that.question-mark
		// $file->format('') what/is-that.question-mark

		$this->assertEquals(
			'test2/test3/what/is-that.question-mark',
			$file->format(2)
		);
		$this->assertEquals(
			'test3/what/is-that.question-mark',
			$file->format(-2)
		);
		$this->assertEquals(
			'what/is-that.question-mark',
			$file->format('tmp/test1/test2/test3')
		);
		$this->assertEquals(
			'what/is-that.question-mark',
			$file->format('/tmp/test1/test2/test3')
		);
		$this->assertEquals(
			'what/is-that.question-mark',
			$file->format('tmp/test1/test2/test3/')
		);
		$this->assertEquals(
			'what/is-that.question-mark',
			$file->format('/tmp/test1/test2/test3/')
		);
		$this->assertNotEquals(
			'what/is-that.question-mark',
			$file->format('/non-matching-string')
		);
		$this->assertEquals(
			'is-that.question-mark',
			$file->format('/non-matching-string')
		);
		$this->assertEquals('is-that.question-mark', $file->format());

		$dir = FS::dir('/tmp/test1/test2/test3/what/test-more/extra-more/target-dir');
		$this->assertEquals(
			'test2/test3/what/test-more/extra-more/target-dir',
			$dir->format(2)
		);
		$this->assertEquals(
			'test-more/extra-more/target-dir',
			$dir->format(-2)
		);
		$this->assertEmpty(FS::dir('')->format(-2));

		$sep = DIRECTORY_SEPARATOR;
		$r_p = realpath(__DIR__."{$sep}..{$sep}..");
		PHP::init(['working_dir' => $r_p]);

		$this->assertInstanceOf(
			File::class,
			FS::locate('tests', 'general', 'FSTest.php')
		);
		$this->assertInstanceOf(
			Dir::class,
			FS::locate('tests', 'general')
		);
		$this->assertEquals(
			"{$r_p}{$sep}tests{$sep}general",
			Str::ing(FS::locate('tests', 'general'))
		);
	}

	/**
	 * @covers \spaf\simputils\models\Dir
	 * @covers \spaf\simputils\components\filters\DirExtFilter
	 * @covers \spaf\simputils\components\filters\OnlyFilesFilter
	 * @covers \spaf\simputils\components\filters\OnlyDirsFilter
	 *
	 * @runInSeparateProcess
	 * @return void
	 * @throws \Exception
	 */
	function testDirs() {
		$wd = realpath(__DIR__.'/../..');
		PHP::init(['working_dir' => $wd]);
		$dd = FS::locate('tests');
		$this->assertInstanceOf(Dir::class, $dd);

		$res = $dd->walk(false, new OnlyFilesFilter);
		$this->assertInstanceOf(Box::class, $res);
		$this->assertEquals(0, $res->size);

		$res = $dd->walk(true, new OnlyDirsFilter());
		$this->assertInstanceOf(Box::class, $res);
		$this->assertEquals(1, $res->size);

		$res = $dd->walk(true, new OnlyFilesFilter);
		$this->assertInstanceOf(Box::class, $res);
		$this->assertGreaterThan(0, $res->size);

		$res = $dd->walk(true, new DirExtFilter('general', 'php'));
		$this->assertInstanceOf(Box::class, $res);
		$this->assertGreaterThan(0, $res->size);

		$res = $dd->walk(true, '#bebeebbebeb#');
		$this->assertEquals(0, $res->size);
	}

//	function testMimeTypeCheck() {
		// FIX  rename(/tmp/dot-dot-dot-test-file-blabla-bla.txt,/tmp/dot-dot-dot-test-file-blabla-bla.csv): No such file or directory
//		$file = FS::file('/tmp/dot-dot-dot-test-file-blabla-bla.txt');
//		$this->assertEquals('application/x-empty', $file->mime_type);
//		$file->move(ext: 'csv');
//
//		$file->content = [[1, 2, 3]];
//
//		$mime = FS::getFileMimeType($file);
//		$this->assertEquals('text/csv', $mime);
//	}
}
