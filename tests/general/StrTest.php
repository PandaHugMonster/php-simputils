<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\Str;
use function strtolower;
use function strtoupper;

/**
 * @covers \spaf\simputils\Str
 *
 * @uses \spaf\simputils\PHP
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
 */
class StrTest extends TestCase {

	/**
	 * @covers \spaf\simputils\Str::isJson
	 * @return void
	 */
	function testStrIsJson() {
		$this->assertTrue(Str::isJson('{"molmo": "toto"}'));
		$this->assertFalse(Str::isJson('TEST TEST TEST'));
		$this->assertFalse(Str::isJson('{molmo:toto}'));
	}

	/**
	 * @covers \spaf\simputils\Str::len
	 * @return void
	 */
	function testLen() {
		$this->assertEquals(10, Str::len("1234567890"));
	}

	/**
	 * @covers \spaf\simputils\Str::from
	 * @return void
	 */
	function testFrom() {
		$bool_var = true;
		$this->assertEquals('true', Str::from($bool_var));
		$bool_var = false;
		$this->assertEquals('false', Str::from($bool_var));

		$int_var = 1;
		$this->assertNotEquals('true', Str::from($int_var));
		$this->assertNotEquals('false', Str::from($int_var));
		$this->assertEquals('1', Str::from($int_var));

		$str_var = 'just a string';
		$this->assertEquals($str_var, Str::from($str_var));
	}

	/**
	 * @covers \spaf\simputils\Str::get
	 * @covers \spaf\simputils\Str::ing
	 * @return void
	 */
	function testGet() {
		$pattern = 'My %s string and %s';
		$res = Str::get($pattern, 'special', 'cat');
		$this->assertEquals('My special string and cat', $res);
	}

	/**
	 * @covers \spaf\simputils\Str::upper
	 * @covers \spaf\simputils\Str::lower
	 * @return void
	 */
	function testLetterCaseTransformation() {
		$str = 'My test string';

		$expected = strtoupper($str);
		$this->assertEquals($expected, Str::upper($str));

		$expected = strtolower($str);
		$this->assertEquals($expected, Str::lower($str));
	}

	function strIsDataProvider() {
		return [
			[true, 'this is string value'],
			[true, '12'],
			[true, ''],
			[true, '0x121212'],
			[true, ""],
			[true, "TeXt"],
			[true, 'true'],
			[true, 'false'],

			[false, 123],
			[false, new Box(['1', '2', '3'])],
			[false, true],
			[false, false],
			[false, -1],
			[false, null],
		];
	}

	/**
	 * @covers \spaf\simputils\Str::is
	 *
	 * @dataProvider strIsDataProvider
	 * @return void
	 */
	function testIs($expected, $value) {
		if ($expected) {
			$this->assertTrue(Str::is($value));
		} else {
			$this->assertFalse(Str::is($value));
		}
	}

	/**
	 * @covers \spaf\simputils\Str::startsWith
	 * @covers \spaf\simputils\Str::endsWith
	 * @covers \spaf\simputils\Str::removeEnding
	 *
	 * @return void
	 */
	function testStartsAndEnds() {

		$this->assertTrue(Str::startsWith(
			'My day starts with the coffee cup',
			'My day starts',
			true
		));
		$this->assertTrue(Str::startsWith(
			'My DAY starts with the coffee cup',
			'my day starts',
			false
		));
		$this->assertFalse(Str::startsWith(
			'My day starts with the coffee cup',
			'day starts'
		));
		$this->assertFalse(Str::startsWith(
			'MY DAY starts with the coffee cup',
			'my day starts'
		));

		$this->assertTrue(Str::endsWith(
			'My day starts with the coffee cup',
			'coffee cup',
			true
		));
		$this->assertTrue(Str::endsWith(
			'My DAY starts with the coffee cup',
			'coffEE Cup',
			false
		));
		$this->assertFalse(Str::endsWith(
			'My day starts with the coffee cup',
			'coffee'
		));
		$this->assertFalse(Str::endsWith(
			'MY DAY starts with the coffee cup',
			'Coffee Cup'
		));

		$str = 'This is the day';
		$this->assertEquals(
			$str,
			Str::removeEnding(
			"{$str}, that we will remember forever",
			', that we will remember forever',
			)
		);

		$this->assertEquals(
			$str,
			Str::removeEnding(
				"{$str}, that we will remember forever",
				', that WE will remember forever',
				false
			)
		);
		$this->assertEquals(
			"{$str}, that we will remember for",
			Str::removeEnding(
				"{$str}, that we will remember forever",
				4
			)
		);
		$this->assertEquals(
			"{$str}, that we will remember forever",
			Str::removeEnding(
				"{$str}, that we will remember forever",
				-21
			)
		);
		$this->assertEquals(
			"{$str}, that we will remember forever",
			Str::removeEnding(
				"{$str}, that we will remember forever",
				''
			)
		);

		// Str::removeStarting

		$str = "TesT LinE With SOmE teXt";
		$this->assertEquals(
			'With SOmE teXt',
			Str::removeStarting(
				$str,
				'test line ',
				false
			)
		);
		$this->assertEquals(
			'With SOmE teXt',
			Str::removeStarting(
				$str,
				10
			)
		);
		$this->assertEquals(
			'TesT LinE With SOmE teXt',
			Str::removeStarting(
				$str,
				-5
			)
		);
		$this->assertEquals(
			'TesT LinE With SOmE teXt',
			Str::removeStarting(
				$str,
				''
			)
		);

	}

	/**
	 * @return void
	 */
	public function testBoolStr(): void {
		$true = Str::from(true);
		$false = Str::from(false);
		$this->assertEquals('true', $true, 'Check if true is true');
		$this->assertEquals('false', $false, 'Check if false is false');
	}

	/**
	 * @return void
	 */
	public function testIsJsonString(): void {
		$json = json_encode([
			'my_field' => 'Some Value',
			'int_value' => 12,
			'boolval' => false,
		]);
		$true = Str::isJson($json);
		$false = Str::isJson($json.'TTTT');
		$this->assertTrue($true, 'Check if json is correct');
		$this->assertFalse($false, 'Check if json is incorrect');
	}
}
