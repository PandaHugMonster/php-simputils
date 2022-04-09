<?php /** @noinspection ALL */

use PHPUnit\Framework\TestCase;
use spaf\simputils\DT;
use spaf\simputils\models\DateInterval;
use spaf\simputils\models\DateTime;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function spaf\simputils\basic\ts;

/**
 *
 * @covers \spaf\simputils\DT
 * @covers \spaf\simputils\generic\fixups\FixUpDateTimeZone
 * @covers \spaf\simputils\generic\fixups\FixUpDateTime
 * @covers \spaf\simputils\generic\fixups\FixUpDateTimePrism
 * @covers \spaf\simputils\generic\fixups\FixUpDatePeriod
 * @covers \spaf\simputils\generic\fixups\FixUpDateInterval
 * @covers \spaf\simputils\models\DateTime
 * @covers \spaf\simputils\models\Date
 * @covers \spaf\simputils\models\Time
 * @covers \spaf\simputils\models\DateInterval
 * @covers \spaf\simputils\models\DatePeriod
 * @covers \spaf\simputils\models\DatePeriod
 *
 * @uses \spaf\simputils\interfaces\helpers\DateTimeHelperInterface
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____PrepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\generic\BasicPrism
 * @uses \spaf\simputils\FS
 * @uses \spaf\simputils\generic\BasicInitConfig
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\components\initblocks\DotEnvInitBlock
 * @uses \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\models\File
 * @uses \spaf\simputils\models\L10n
 * @uses \spaf\simputils\models\PhpInfo
 * @uses \spaf\simputils\models\files\apps\DotEnvProcessor
 * @uses \spaf\simputils\models\files\apps\JsonProcessor
 * @uses \spaf\simputils\models\files\apps\TextProcessor
 * @uses \spaf\simputils\models\files\apps\settings\DotEnvSettings
 */
class DateTimeTest extends TestCase {

	/**
	 * @runInSeparateProcess
	 * @return void
	 * @throws \Exception
	 */
	public function testHelperTransparentParsing(): void {
		$dt_class = PHP::redef(DateTime::class);

		$dt = DT::normalize('22.02.1990', 'America/New_York');
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals(1990, $dt->format('Y'), 'Year check');

		$dt = DT::normalize('22.02.1990');
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals(1990, $dt->format('Y'), 'Year check');

		$dt = DT::normalize('22.02.1990', fmt: 'd.m.Y');
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals(02, $dt->format('m'), 'Month check');

		$dt = DT::normalize(123, 'UTC');
		$this->assertInstanceOf($dt_class, $dt, 'Object type check');
		$this->assertEquals(
			'1970-01-01 00:02:03.000000',
			$dt->format(DT::FMT_DATETIME_FULL),
			'Comparing datetime from int'
		);

		$dt_cloned = DT::normalize($dt);
		$this->assertInstanceOf($dt_class, $dt_cloned, 'Object type check');
		$this->assertEquals($dt, $dt_cloned, 'Comparing datetime objects');

		$dt_str = DT::stringify('1970-12-31');
		$this->assertIsString($dt_str, 'Check if it is a string');
		$this->assertEquals(
			'1970-12-31 00:00:00.000000',
			$dt_str,
			'Comparing datetime objects'
		);

		$dt_expected = [
			'2022-02-24', '2022-02-25', '2022-02-26', '2022-02-27', '2022-02-28',
			'2022-03-01', '2022-03-02', '2022-03-03', '2022-03-04', '2022-03-05',
			'2022-03-06', '2022-03-07', '2022-03-08', '2022-03-09', '2022-03-10',
			'2022-03-11', '2022-03-12', '2022-03-13', '2022-03-14', '2022-03-15',
			'2022-03-16', '2022-03-17', '2022-03-18', '2022-03-19', '2022-03-20',
			'2022-03-21', '2022-03-22', '2022-03-23', '2022-03-24', '2022-03-25',
			'2022-03-26', '2022-03-27', '2022-03-28', '2022-03-29', '2022-03-30',
		];

		$dt_period = DT::walk('2022-02-24', '2022-03-30', '1 day');
		// IMP  Stop the war! Save Ukraine! Slava Ukraini!
		foreach ($dt_period as $day) {
			/** @var \DatePeriod $day */
			$this->assertContains("{$day->date}", $dt_expected);
		}
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
		$now = DT::now('UTC');
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
		$dt = ts('2022-01-03 22:00:15', 'UTC');
		$this->assertEquals('2022-01-03', $dt->date);
		$this->assertEquals('22:00:15', $dt->time);
	}

	/**
	 *
	 * @return void
	 * @throws \Exception
	 * @runInSeparateProcess
	 */
	function testOverallDateTimeObject() {
		PHP::init([
			'l10n' => 'ru',
		]);
		$dt = DT::ts('2022-03-31 00:47:58.123456', 'UTC');

		$this->assertEquals(2022, $dt->year);
		$dt->year = 3000;
		$this->assertEquals(3000, $dt->year);

		$this->assertEquals(3, $dt->month);
		$dt->month = 5;
		$this->assertEquals(5, $dt->month);

		$this->assertEquals(31, $dt->day);
		$dt->day = 12;
		$this->assertEquals(12, $dt->day);

		$this->assertEquals(0, $dt->hour);
		$dt->hour = 2;
		$this->assertEquals(2, $dt->hour);

		$this->assertEquals(47, $dt->minute);
		$dt->minute = 33;
		$this->assertEquals(33, $dt->minute);

		$this->assertEquals(58, $dt->second);
		$dt->second = 19;
		$this->assertEquals(19, $dt->second);

		$this->assertEquals(123456, $dt->micro);
		$dt->micro = 234567;
		$this->assertEquals(234567, $dt->micro);

		// Keep in mind, that all of the above now having different from the original
		// date-time string, because those were redefined in the previous tests.
		$this->assertEquals(234, $dt->milli);
		$this->assertEquals(20, $dt->week);
		$this->assertEquals(1, $dt->dow);
		$this->assertEquals(131, $dt->doy);

		// Keep in mind, it's a json value, not just a string, this is why double-quotes
		$this->assertEquals('"3000-05-12 02:33:19.234567"', $dt->toJson());

		// NOTE At this point original value is 3000-05-12 02:33:19.234567

		$dt->add('12 days 010101 microseconds')
			->sub('18 seconds 244668 microseconds')
			->modify('-30 mins');

		$this->assertEquals('3000-05-24 02:03:01.000000', $dt->for_system);
		$this->assertEquals('24.05.3000 02:03', $dt->for_user);

		$this->assertEquals(
			'- 11 days 23 hours 29 minutes 41 seconds 765433 microseconds',
			"{$dt->diff()}"
		);

		$this->assertEquals(
			'24.05.3000 02:03 - 01.01.3001 03:00',
			"{$dt->walk('3001-01-01', '1 month')}"
		);

		$this->assertEquals(
			'24.05.3000 02:03 - 01.01.3001 03:00',
			"{$dt->walk('3001-01-01', new DateInterval('P1M'))}"
		);

		$this->assertNotEmpty($dt->orig_value);
		$this->assertEquals(
			'3000-05-12 02:33:19.234567',
			"{$dt->orig_value->for_system}"
		);
	}

	function testDateAndTimePrisms() {
		$dt = DT::ts('2022-03-31 00:47:58.123456', 'UTC');

		// Simple check
		$this->assertEquals('2022-03-31', $dt->date->for_system);
		$this->assertEquals('00:47:58', $dt->time->for_system);

		// Modifying the main object, results are seen on the prisms
		$dt->add('-200 minutes');
		$this->assertEquals('2022-03-30', $dt->date->for_system);
		$this->assertEquals('21:27:58', $dt->time->for_system);
	}

	function testOther() {
		$tz_default = DT::getDefaultTimeZone();

		$dt = DT::ts('2022-12-25 05:04:03');
		$this->assertEquals($tz_default, $dt->tz);

		DT::setDefaultTimeZone('Asia/Novosibirsk');

		$this->assertNotEquals($dt->tz, DT::getDefaultTimeZone());

		$dt = DT::ts('2022-12-25 05:04:03');
		$this->assertEquals($dt->tz, DT::getDefaultTimeZone());

		$dt = DT::ts('2022-12-25 05:04:03', 'UTC');
		$this->assertEquals(
			$dt->for_user,
			$dt->getForSystemObj()->format(DateTime::$l10n_user_datetime_format)
		);

		$dt = DT::ts('2022-12-25 05:04:03', true);
		$this->assertNotEquals(
			$dt->for_user,
			$dt->getForSystemObj()->format(DateTime::$l10n_user_datetime_format)
		);
	}
}
