<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\Str;

/**
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\traits\helpers\DateTimeTrait
 * @uses \spaf\simputils\models\DateTime
 * @uses \spaf\simputils\interfaces\helpers\DateTimeHelperInterface
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
}
