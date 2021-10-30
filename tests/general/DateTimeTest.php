<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\models\DateTime;

/**
 *
 * @covers \spaf\simputils\helpers\DateTimeHelper
 * @covers \spaf\simputils\traits\helpers\DateTimeTrait
 * @uses \spaf\simputils\interfaces\helpers\DateTimeHelperInterface
 * @covers \spaf\simputils\models\DateTime
 */
class DateTimeTest extends TestCase {

	public function testHelperTransparentParsing(): void {
		$dt = DateTimeHelper::normalize('22.02.1990');
		$this->assertInstanceOf(DateTime::class, $dt, 'Object type check');
		$this->assertEquals(1990, $dt->format('Y'), 'Year check');

		$dt = DateTimeHelper::normalize('22.02.1990', fmt: 'd.m.Y');
		$this->assertInstanceOf(DateTime::class, $dt, 'Object type check');
		$this->assertEquals(02, $dt->format('m'), 'Month check');

		$dt = DateTimeHelper::normalize(123);
		$this->assertInstanceOf(DateTime::class, $dt, 'Object type check');
		$this->assertEquals('1970-01-01 00:02:03.000000', $dt->format(DateTimeHelper::FMT_DATETIME_FULL),
			'Comparing datetime from int');

		$dt_cloned = DateTimeHelper::normalize($dt);
		$this->assertInstanceOf(DateTime::class, $dt_cloned, 'Object type check');
		$this->assertEquals($dt, $dt_cloned, 'Comparing datetime objects');

		$dt_str = DateTimeHelper::stringify('1970-12-31');
		$this->assertIsString($dt_str, 'Check if it is a string');
		$this->assertEquals('1970-12-31 00:00:00.000000', $dt_str, 'Comparing datetime objects');
	}

	public function testNowObjectCreation(): void {
		$dt = DateTimeHelper::now();
		$this->assertInstanceOf(DateTime::class, $dt, 'Object type check');

		DateTimeHelper::$now_string = '01.02.2001';

		$dt = DateTimeHelper::now();
		$this->assertInstanceOf(DateTime::class, $dt, 'Object type check');
		$this->assertEquals(2001, $dt->format('Y'), 'Is faked year was used');
		$this->assertEquals(2, $dt->format('m'), 'Is faked month was used');
		$this->assertEquals(1, $dt->format('d'), 'Is faked day was used');

		DateTimeHelper::$now_string = null;
	}

	public function testTransparentStringifyingDateTimeObject() {
		$now = DateTimeHelper::now();
		$this->assertInstanceOf(DateTime::class, $now, 'Is a date-time object');
		$this->assertEquals(DateTimeHelper::stringify($now), strval($now), 'Is a string-compatible');
	}
}
