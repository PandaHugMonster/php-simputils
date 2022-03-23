<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\exceptions\NonExistingDataUnit;
use spaf\simputils\exceptions\UnspecifiedDataUnit;
use spaf\simputils\models\BigNumber;
use spaf\simputils\models\DataUnit;

/**
 * @covers \spaf\simputils\Data
 */
class DataHelperTest extends TestCase {

	protected function setUp(): void {
		// BigNumber::$default_extension = BigNumber::SUBSYSTEM_BCMATH;
	}

	public function dataProviderUnitTo(): array {
		return [
			// in, expected, unit
			[ '12mb', new BigNumber(12288), 'kb' ],
			[ '150kb', new BigNumber(0.15), 'mb' ],
		];
	}

	public function dataProviderBytesTo(): array {
		return [
			// in, expected, unit
			[ 1024, new BigNumber(1), 'kb' ],
			[ 1500, new BigNumber(1.46), 'Kb' ],
			[ 15000000, new BigNumber(14.31), 'mb' ],
			[ 15000000, new BigNumber(15000000), 'b' ],
		];
	}

	public function dataProviderToBytes(): array {
		return [
			// in, expected
			[ '1024kb', new BigNumber('1048576') ],
			[ '1024b', new BigNumber('1024') ],
			[ '16mb', new BigNumber('16777216') ],
			[ '1pb', new BigNumber('1125899906842624') ],
			[ '1gb', new BigNumber('1073741824') ],
		];
	}

	public function dataProviderHumanReadable(): array {
		return [
			// in, expected
			[ '1023kb', '1023 KB' ],
			[ '10240ZB', '10 YB' ],
		];
	}

	/**
	 * @dataProvider dataProviderUnitTo
	 * @return void
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function testConversionUnitTo($in, $expected, $unit) {
		$res = DataUnit::unitTo($in, $unit);
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
		$res = DataUnit::bytesTo($in, $unit);
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
		$res = DataUnit::toBytes($in);
		$this->assertEquals($expected, $res);
	}

	/**
	 * @dataProvider dataProviderHumanReadable
	 * @return void
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function testConversionHumanReadable($in, $expected) {
		$res = DataUnit::humanReadable($in);
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
		DataUnit::clearUnit($val);
	}
}
