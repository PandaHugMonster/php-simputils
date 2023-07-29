<?php /** @noinspection ALL */

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use spaf\simputils\models\Set;
use spaf\simputils\models\StackFifo;
use spaf\simputils\models\StackLifo;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function implode;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\dr;
use function spaf\simputils\basic\env;
use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\path;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\prstr;
use function spaf\simputils\basic\stack;
use function spaf\simputils\basic\ts;

/**
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\models\DateTime
 * @uses \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\models\File
 * @uses \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\models\files\apps\TextProcessor
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\generic\fixups\FixUpDateTime
 * @uses \spaf\simputils\generic\BasicPrism
 * @uses \spaf\simputils\generic\fixups\FixUpDateTimePrism
 * @uses \spaf\simputils\generic\fixups\FixUpDateTimeZone
 * @uses \spaf\simputils\models\Date
 * @uses \spaf\simputils\models\Time
 */
class ShortcutsTest extends TestCase {

	/**
	 * @covers \spaf\simputils\basic\bx
	 *
	 * @uses   \spaf\simputils\PHP::box
	 *
	 * @return void
	 */
	function testBox() {
		$box_class = PHP::redef(Box::class);

		$a = bx(['my', 'special', 'boxy']);

		// Check instance
		$this->assertInstanceOf($box_class, $a);

		// Check amount of elements
		$this->assertEquals(3, $a->size);

		// Check elements one by one
		$this->assertEquals('my', $a[0]);
		$this->assertEquals('special', $a[1]);
		$this->assertEquals('boxy', $a[2]);

	}

	/**
	 *
	 * @covers \spaf\simputils\basic\now
	 * @uses \spaf\simputils\DT
	 *
	 * @return void
	 */
	function testNow() {
		$dt_class = PHP::redef(DateTime::class);

		$dt = now();

		$this->assertInstanceOf($dt_class, $dt);
	}

	/**
	 *
	 * @covers \spaf\simputils\basic\ts
	 *
	 * @uses \spaf\simputils\DT
	 *
	 * @return void
	 */
	function testTs() {
		$dt_class = PHP::redef(DateTime::class);

		$fmt_date = '2022-01-03';
		$fmt_time = '12:13:16';

		$format1 = "{$fmt_date} {$fmt_time}";
		$dt = ts($format1, 'UTC');

		$this->assertInstanceOf($dt_class, $dt);
		$this->assertEquals("{$fmt_time} {$fmt_date}", "{$dt->time} {$dt->date}");
	}

	/**
	 *
	 * @covers \spaf\simputils\basic\fl
	 * @uses \spaf\simputils\FS::file
	 * @return void
	 */
	function testFl() {
		$file_class = PHP::redef(File::class);

		$memory_file = fl();

		$this->assertInstanceOf($file_class, $memory_file);
		$this->assertEquals(0, $memory_file->size);

		$example = 'Really awesome content';

		$memory_file->content = $example;
		$this->assertEquals($example, $memory_file->content);
		$this->assertEquals(Str::len($example), $memory_file->size);
	}

	/**
	 * @covers \spaf\simputils\basic\env
	 * @uses \spaf\simputils\PHP::env
	 * @uses \spaf\simputils\PHP::allEnvs
	 * @return void
	 */
	function testEnv() {
		$key = 'TP_TP';
		$_ENV[$key] = 'TOOT';

		$this->assertEquals($_ENV[$key], env($key));
	}

	/**
	 * @covers \spaf\simputils\basic\pd
	 * @uses \spaf\simputils\PHP::pd
	 * @uses \spaf\simputils\PHP::pr
	 * @return void
	 */
	function testPd() {
		PHP::$allow_dying = false;

		$str = 'This should be printed, is it?';
		pd($str);
		pd($str);
		$this->expectOutputString("$str\n$str\n");
		PHP::$allow_dying = true;
	}

	/**
	 * @covers \spaf\simputils\basic\stack
	 *
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\models\StackFifo
	 * @uses \spaf\simputils\models\StackLifo
	 *
	 * @return void
	 */
	function testStacks() {

		$stack = stack([1, 2, 3, 4]);
		$this->assertInstanceOf(StackLifo::class, $stack);

		$stack = stack([1, 2, 3, 4], type: PHP::STACK_FIFO);
		$this->assertInstanceOf(StackFifo::class, $stack);

	}

	/**
	 * @covers \spaf\simputils\basic\dr
	 *
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\FS
	 * @uses \spaf\simputils\models\Dir
	 *
	 * @return void
	 */
	function testDr() {

		$dir = dr('/tmp');
		$this->assertInstanceOf(Dir::class, $dir);

	}

	/**
	 * @covers \spaf\simputils\basic\pr
	 *
	 * @uses \spaf\simputils\PHP
	 *
	 * @return void
	 */
	function testPr() {

		$test_str = 'Stand With Ukraine!';
		$this->expectOutputString("{$test_str}\n");

		pr($test_str);

	}

	/**
	 * @covers \spaf\simputils\basic\prstr
	 *
	 * @uses \spaf\simputils\PHP
	 *
	 * @return void
	 */
	function testPrStr() {

		$test_str = 'My life was fine, until the war has come! :( ';

		$res = prstr($test_str);

		$this->assertIsString($res);
		$this->assertNotEmpty($res);
		$this->assertEquals("{$test_str}\n", $res);

	}

	/**
	 * @covers \spaf\simputils\basic\path
	 *
	 * @uses \spaf\simputils\FS
	 *
	 * @uses \spaf\simputils\basic\bx
	 * @return void
	 */
	function testPath() {
		$lines = ['path1', 'path2', 'path3'];
		$expected = implode('/', $lines);

		$res = path(...$lines);
		$this->assertEquals($expected, $res);
	}

	/**
	 * @covers \spaf\simputils\PHP::set
	 * @uses \spaf\simputils\models\Set
	 *
	 * @return void
	 */
	function testPHPSet() {
		$set = PHP::set([1, 2, 3, 3, 4, 4]);
		$this->assertInstanceOf(Set::class, $set);
	}
}
