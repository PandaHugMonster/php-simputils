<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\exceptions\NonExistingDataUnit;
use spaf\simputils\exceptions\UnspecifiedDataUnit;
use spaf\simputils\helpers\DataHelper;

/**
 * @covers \spaf\simputils\helpers\DataHelper
 */
class DataHelperTest extends TestCase {

	public function dataProviderUnitTo(): array {
		return [
			// in, expected, unit
			[ '12mb', 12288, 'kb' ],
			[ '150kb', 0.15, 'mb' ],
		];
	}

	public function dataProviderBytesTo(): array {
		return [
			// in, expected, unit
			[ 1024, 1, 'kb' ],
			[ 1500, 1.46, 'Kb' ],
			[ 15000000, 14.31, 'mb' ],
			[ 15000000, 15000000, 'b' ],
		];
	}

	public function dataProviderToBytes(): array {
		return [
			// in, expected
			[ '1024kb', 1048576 ],
			[ '1024b', 1024 ],
			[ '16mb', 16777216 ],
			[ '1pb', 1125899906842624 ],
			[ '1.9gb', 2040109466 ],
		];
	}

	public function dataProviderHumanReadable(): array {
		return [
			// in, expected
			[ '1023kb', '1023KB' ],
			[ '10240ZB', '10YB' ],
		];
	}

	/**
	 * @dataProvider dataProviderUnitTo
	 * @return void
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function testConversionUnitTo($in, $expected, $unit) {
		$res = DataHelper::unitTo($in, $unit);
		$this->assertEquals($expected, $res);
	}

	/**
	 * @param $in
	 * @param $expected
	 * @param $unit
	 *
	 * @dataProvider dataProviderBytesTo
	 * @return void
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function testConversionBytesTo($in, $expected, $unit) {
		$res = DataHelper::bytesTo($in, $unit);
		$this->assertEquals($expected, $res);
	}

	/**
	 * @param $in
	 * @param $expected
	 *
	 * @dataProvider dataProviderToBytes
	 * @return void
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function testConversionToBytes($in, $expected) {
		$res = DataHelper::toBytes($in);
		$this->assertEquals($expected, $res);
	}

	/**
	 * @dataProvider dataProviderHumanReadable
	 * @return void
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function testConversionHumanReadable($in, $expected) {
		$res = DataHelper::humanReadable($in);
		$this->assertEquals($expected, $res);
	}

	public function dataProviderExceptions() {
		return [
			[ '1515', UnspecifiedDataUnit::class ],
			[ '1024qq', NonExistingDataUnit::class ],
		];
	}

	/**
	 *
	 * @dataProvider dataProviderExceptions
	 * @runInSeparateProcess
	 * @return void
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function testExceptions($val, $exception_class) {
		$this->expectException($exception_class);
		DataHelper::clearUnit($val);
	}
}
