<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\components\normalizers\BooleanNormalizer;
use spaf\simputils\components\normalizers\DataUnitNormalizer;
use spaf\simputils\components\normalizers\DateTimeNormalizer;
use spaf\simputils\components\normalizers\FloatNormalizer;
use spaf\simputils\components\normalizers\IntegerNormalizer;
use spaf\simputils\components\normalizers\LowerCaseNormalizer;
use spaf\simputils\components\normalizers\StringNormalizer;
use spaf\simputils\components\normalizers\UpperCaseNormalizer;
use spaf\simputils\models\DataUnit;
use spaf\simputils\models\DateTime;

/**
 *
 *
 */
class ValidatorsTest extends TestCase {

	public function booleanNormalizerData() {
		return [
			// True
			[true, true],
			[true, 1],
			[true, 'true'],
			[true, 'yes'],
			[true, 'y'],
			[true, 't'],
			[true, '1'],

			// False
			[false, false],
			[false, 0],
			[false, null],
			[false, 'false'],
			[false, 'no'],
			[false, 'n'],
			[false, 'f'],
			[false, '0'],
			[false, 'null'],
			[false, ''],
		];
	}

	public function floatNormalizerData() {
		return [
			[99.92, '99.92'],
			[0.0, 'test'],
			[15.0, 15],
			[1.0, true],
			[0.0, false],
			[0.0, null],
		];
	}

	public function integerNormalizerData() {
		return [
			[99, '99.92'],
			[12, '012'],
			[0, 'test'],
			[15, 15],
			[1, true],
			[0, false],
			[0, null],
		];
	}

	public function stringNormalizerData() {
		return [
			['100500', 100500],
			['500.009', 500.009],
			['true', true],
			['false', false],
			['', null],
		];
	}

	/**
	 * @covers \spaf\simputils\components\normalizers\BooleanNormalizer
	 * @covers \spaf\simputils\Boolean::from
	 *
	 * @uses \spaf\simputils\Str
	 *
	 * @dataProvider booleanNormalizerData
	 * @return void
	 */
	function testBooleanNormalizers($expected, $val) {
		$this->assertEquals($expected, BooleanNormalizer::process($val));
	}

	/**
	 * @covers \spaf\simputils\components\normalizers\DateTimeNormalizer
	 *
	 * @uses \spaf\simputils\DT
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\generic\fixups\FixUpDateTimeZone
	 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
	 * @uses \spaf\simputils\models\DateTime
	 * @uses \spaf\simputils\traits\PropertiesTrait::__get
	 *
	 * @return void
	 */
	function testDateTimeNormalizers() {
		$dt = DateTimeNormalizer::process('2020-02-02 00:01:02');
		$this->assertInstanceOf(DateTime::class, $dt);
	}

	/**
	 * @covers \spaf\simputils\components\normalizers\FloatNormalizer
	 * @dataProvider floatNormalizerData
	 * @return void
	 */
	function testFloatNormalizers($expected, $val) {
		$this->assertIsFloat($v = FloatNormalizer::process($val));
		$this->assertEquals($expected, $v);
	}

	/**
	 * @covers \spaf\simputils\components\normalizers\IntegerNormalizer
	 * @dataProvider integerNormalizerData
	 * @return void
	 */
	function testIntegerNormalizers($expected, $val) {
		$this->assertIsInt($v = IntegerNormalizer::process($val));
		$this->assertEquals($expected, $v);
	}

	/**
	 *
	 * @covers \spaf\simputils\components\normalizers\StringNormalizer
	 * @dataProvider stringNormalizerData
	 *
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\Boolean
	 * @return void
	 */
	function testStringNormalizers($expected, $val) {
		$this->assertIsString($v = StringNormalizer::process($val));
		$this->assertEquals($expected, $v);
	}

	/**
	 * @covers \spaf\simputils\components\normalizers\UpperCaseNormalizer
	 * @covers \spaf\simputils\components\normalizers\LowerCaseNormalizer
	 * @covers \spaf\simputils\components\normalizers\DataUnitNormalizer
	 *
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\models\DataUnit
	 * @uses \spaf\simputils\attributes\Property
	 * @uses \spaf\simputils\models\BigNumber
	 * @uses \spaf\simputils\models\Box
	 * @uses \spaf\simputils\Data
	 * @uses \spaf\simputils\models\PhpInfo
	 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
	 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
	 * @uses \spaf\simputils\traits\SimpleObjectTrait::__set
	 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsPrepareProperty
	 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsPropertyBatchMethodGet
	 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
	 * @uses \spaf\simputils\Boolean
	 *
	 *
	 * @return void
	 */
	function testAdditionalNormalizers() {
		$this->assertIsString($v = UpperCaseNormalizer::process('test text'));
		$this->assertEquals('TEST TEXT', $v);

		$this->assertIsString($v = UpperCaseNormalizer::process('teST TExt'));
		$this->assertEquals('TEST TEXT', $v);

		$this->assertIsString($v = UpperCaseNormalizer::process(12.45));
		$this->assertEquals('12.45', $v);

		$this->assertIsString($v = UpperCaseNormalizer::process(true));
		$this->assertEquals('TRUE', $v);

		////

		$this->assertIsString($v = LowerCaseNormalizer::process('TEST TEXT'));
		$this->assertEquals('test text', $v);

		$this->assertIsString($v = LowerCaseNormalizer::process('teST TExt'));
		$this->assertEquals('test text', $v);

		$this->assertIsString($v = LowerCaseNormalizer::process(12.45));
		$this->assertEquals('12.45', $v);

		$this->assertIsString($v = LowerCaseNormalizer::process(false));
		$this->assertEquals('false', $v);

		$this->assertInstanceOf(DataUnit::class, $v = DataUnitNormalizer::process('1234'));
		/** @var DataUnit $v */
		$this->assertEquals(1234, $v->for_system);
	}
}
