<?php

namespace general;

use Closure;
use Exception;
use PHPUnit\Framework\TestCase;
use spaf\simputils\FS;
use spaf\simputils\models\File;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use ValueError;
use function fclose;
use function file_get_contents;
use function fopen;
use function fwrite;

/**
 * @covers \spaf\simputils\models\File
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @covers \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\models\files\apps\TextProcessor
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__set
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____prepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__isset
 * @covers \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\FS
 * @uses \spaf\simputils\attributes\Property
 */
class FileModelTest extends TestCase {

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	function testBasicsOfFileOperation() {
		$file_class = PHP::redef(File::class);
		// Creating in memory file
		$file = PHP::file();

		$content = 'Some string content here';
		$file->content = $content;

		$this->assertInstanceOf($file_class, $file);
		$this->assertEquals($content, $file->content);
		$this->assertEquals(Str::len($content), $file->size);

		// Switch "in-memory" file into a regular "in-file-system" file
		$file->move(
			'/tmp',
			'new-special-file-created-from-memory',
			'txt',
			true
		);

		$this->assertEquals(
			$file_first_location = '/tmp/new-special-file-created-from-memory.txt', $file->name_full
		);
		$this->assertFileExists($file->name_full);

		$new_file_obj = $file->copy(name: 'copied-file', ext: 'csv', overwrite: true);

		$this->assertNotEquals('/tmp/copied-file.csv', $file->name_full);
		$this->assertEquals('/tmp/copied-file.csv', $new_file_obj->name_full);

		// Moving now file from one FS location to another

		$file->move(name: 'newly-moved-file-from-fs-to-fs', ext: 'json', overwrite: true);

		$this->assertFileDoesNotExist($file_first_location);
		$this->assertFileExists($file->name_full);

		$file->delete(true);
		$this->assertFileDoesNotExist($file->name_full);
		$new_file_obj->delete(true);
		$this->assertFileDoesNotExist($new_file_obj->name_full);


		// Again in-memory file
		$file = new File();

		$file->content = 'One more newly created file';

		$new_file_obj = $file->copy('/tmp', 'ttt-one-more-time', 'txt', true);

		$this->assertNotEquals($file->obj_id, $new_file_obj->obj_id);
		$this->assertEquals($file->obj_type, $new_file_obj->obj_type);

		$this->assertEquals($file->size, $new_file_obj->size);

		// Here file is virtual, so it does not exist
		$this->assertFalse($file->exists);

		// Here it is a real file
		$this->assertTrue($new_file_obj->exists);


		$file_is_not_copied = $file->copy(
			'/tmp', 'ttt-one-more-time', 'txt', false
		);

		$this->assertEmpty($file_is_not_copied);

		$this->assertFalse($new_file_obj->delete(false));
		$new_file_obj->delete(true);
		$this->assertFileDoesNotExist($new_file_obj->name_full);

		$this->expectException(Exception::class);
		$file->copy($file->name_full);
	}

	function testAdditionalFileStuff() {
		$file = PHP::file();
		$file->content = 'totoro';

		$prefix = 'Our amazing guest: ';
		$string_casting = "{$prefix}{$file}";
		$this->assertStringStartsWith($prefix, $string_casting);

		// Custom file app
		$fake_read = 'read: FAKED CONTENT! ^_^';
		$fake_write = 'written: ANOTHER FAKED CONTENT! %_%';

		$file = PHP::file(
			'/tmp/temp-file-simputils-test',
			app: function ($self, $fd, $is_reading, $data) use ($fake_read, $fake_write) {
				if ($is_reading) {
					return $fake_read;
				} else {
					fwrite($fd, $fake_write);
				}
			}
		);
		$file->content = 'totoro';

		$this->assertEquals($fake_write, file_get_contents($file->name_full));
		$this->assertEquals($fake_read, $fake_read);

		$file->delete(true);

		// Other constructor coverage
		$fd = fopen('/tmp/again-testing-simputils-file-bla-bla-bla.txt', 'w+');
		$file = PHP::file($fd);
		$content = 'Let\'s write something cool into a file descriptor';
		$file->content = $content;
		$this->assertEquals(Str::len($content), $file->size);
		fclose($fd);

		$file1 = new File('/tmp/again-testing-simputils-file-bla-bla-bla.txt');
		$file2 = new File($file1);
		$this->assertEquals($file2->name_full, $file1->name_full);
		$this->assertNotEquals($file2->obj_id, $file1->obj_id);

		$file1->delete(true);

		$fd = fopen('/tmp/again-testing-simputils-file-bla-bla-bla.txt', 'w+');
		$file1 = new File($fd);
		$file2 = new File($file1);
		$this->assertEquals($file2->fd, $file1->fd);
		$this->assertNotEquals($file2->obj_id, $file1->obj_id);
		fclose($fd);

		FS::rmFile('/tmp/again-testing-simputils-file-bla-bla-bla.txt');

		$file1->app = Closure::fromCallable(function ($self, $fd, $is_reading, $data) {

		});

		$this->assertTrue($file1->app instanceof Closure);

		$file_dev_null = PHP::file('/dev/null');
		$this->assertEmpty($file_dev_null->content);

		$file_non_existing_one = PHP::file('null100500----___');
		$this->assertEmpty($file_non_existing_one->content);
	}

	function testConstructorValueError() {
		$this->expectException(ValueError::class);
		$file = new File(33);
	}
}
