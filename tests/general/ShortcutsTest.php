<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\File;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function spaf\simputils\basic\box;
use function spaf\simputils\basic\env;
use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\ts;

/**
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\models\DateTime
 * @uses \spaf\simputils\interfaces\helpers\DateTimeHelperInterface
 * @uses \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\models\File
 * @uses \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\models\files\apps\TextProcessor
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\PHP::isClass
 * @uses \spaf\simputils\PHP::redef
 * @uses \spaf\simputils\generic\fixups\FixUpDateTime
 */
class ShortcutsTest extends TestCase {

	/**
	 * @covers \spaf\simputils\basic\box
	 * @covers \spaf\simputils\PHP::box
	 * @return void
	 */
	function testBox() {
		$box_class = PHP::redef(Box::class);

		$a = box(['my', 'special', 'boxy']);

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
	 * @covers \spaf\simputils\PHP::now
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
	 * @covers \spaf\simputils\PHP::ts
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
		$dt = ts($format1);

		$this->assertInstanceOf($dt_class, $dt);
		$this->assertEquals("{$fmt_time} {$fmt_date}", "{$dt->time} {$dt->date}");
	}

	/**
	 *
	 * @covers \spaf\simputils\basic\fl
	 * @covers \spaf\simputils\PHP::file
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
	 * @covers \spaf\simputils\PHP::env
	 * @covers \spaf\simputils\PHP::allEnvs
	 * @return void
	 */
	function testEnv() {
		$key = 'TP_TP';
		$_ENV[$key] = 'TOOT';

		$this->assertEquals($_ENV, (array) env());
		$this->assertEquals($_ENV[$key], env($key));
	}

	/**
	 * @covers \spaf\simputils\basic\pd
	 * @covers \spaf\simputils\PHP::pd
	 * @covers \spaf\simputils\PHP::pr
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
}
