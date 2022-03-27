<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\DT;
use spaf\simputils\models\DateTime;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function spaf\simputils\basic\ts;

/**
 *
 * @covers \spaf\simputils\DT
 * @covers \spaf\simputils\models\DateTime
 * @covers \spaf\simputils\generic\fixups\FixUpDateTimeZone
 * @covers \spaf\simputils\generic\fixups\FixUpDateTime
 * @covers \spaf\simputils\generic\fixups\FixUpDateTimePrism
 * @covers \spaf\simputils\models\Date
 * @covers \spaf\simputils\models\Time
 *
 * @uses \spaf\simputils\interfaces\helpers\DateTimeHelperInterface
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____prepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\generic\BasicPrism
 */
class DateTimeTest extends TestCase {

	public function testHelperTransparentParsing(): void {
		$dt_class = PHP::redef(DateTime::class);

		$dt = DT::normalize('22.02.1990');
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals(1990, $dt->format('Y'), 'Year check');

		$dt = DT::normalize('22.02.1990', fmt: 'd.m.Y');
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals(02, $dt->format('m'), 'Month check');

		$dt = DT::normalize(123);
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals('1970-01-01 00:02:03.000000', $dt->format(DT::FMT_DATETIME_FULL),
			'Comparing datetime from int');

		$dt_cloned = DT::normalize($dt);
		$this->assertInstanceOf($dt_class, $dt_cloned, 'Object type check');
		$this->assertEquals($dt, $dt_cloned, 'Comparing datetime objects');

		$dt_str = DT::stringify('1970-12-31');
		$this->assertIsString($dt_str, 'Check if it is a string');
		$this->assertEquals('1970-12-31 00:00:00.000000', $dt_str, 'Comparing datetime objects');
	}

	public function testNowObjectCreation(): void {
		$dt_class = PHP::redef(DateTime::class);
		$dt = DT::now();
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');

		DT::$now_string = '01.02.2001';

		$dt = DT::now();
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals(2001, $dt->format('Y'), 'Is faked year was used');
		$this->assertEquals(2, $dt->format('m'), 'Is faked month was used');
		$this->assertEquals(1, $dt->format('d'), 'Is faked day was used');

		DT::$now_string = null;
	}

	public function testTransparentStringifyingDateTimeObject() {
		$dt_class = PHP::redef(DateTime::class);
		$now = DT::now();
		$this->assertInstanceOf($dt_class, $now, 'Is a date-time object');
		$this->assertEquals(
			DT::stringify($now),
			Str::ing($now->for_system),
			'Is a string-compatible'
		);
	}

	/**
	 * @uses \spaf\simputils\basic\ts
	 * @return void
	 */
	function testDateAndTimeProperties() {
		$dt = ts('2022-01-03 22:00:15');
		$this->assertEquals('2022-01-03', $dt->date);
		$this->assertEquals('22:00:15', $dt->time);
	}
}
