<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\components\SimpleObject;
use spaf\simputils\exceptions\PropertyAccessError;
use spaf\simputils\Settings;

/**
 * @property ?string $val1
 * @property int $val2
 * @property bool $val3
 */
class MyModelExample extends SimpleObject {

	protected ?string $_val1 = null;
	protected int $_val2 = 0;
	protected bool $_val3 = false;

	public function getVal5(): ?string {
		return $this->_val1;
	}
	public function setVal6(?string $val) {
		$this->_val1 = $val;
	}

	public function getVal1(): ?string {
		return $this->_val1;
	}
	public function setVal1(?string $val) {
		$this->_val1 = $val;
	}
	protected function get_val1(): ?string {
		return $this->_val1;
	}
	protected function set_val1(?string $val) {
		$this->_val1 = $val;
	}

	public function getVal2(): int {
		return $this->_val2;
	}
	public function setVal2(int $val) {
		$this->_val2 = $val;
	}
	protected function get_val2(): int {
		return $this->_val2;
	}
	protected function set_val2(int $val) {
		$this->_val2 = $val;
	}

	public function getVal3(): bool {
		return $this->_val3;
	}
	public function setVal3(bool $val) {
		$this->_val3 = $val;
	}
	protected function get_val3(): bool {
		return $this->_val3;
	}
	protected function set_val3(bool $val) {
		$this->_val3 = $val;
	}
}

/**
 * @covers \spaf\simputils\components\SimpleObject
 * @covers \spaf\simputils\traits\MetaMagic
 * @covers \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\Settings
 */
class ModelStructureTest extends TestCase {

	public function dataProviderExceptions() {
		return [
			[ 'val4', 'get', PropertyAccessError::class ],
			[ 'val6', 'get', PropertyAccessError::class ],
			[ 'val4', 'set', PropertyAccessError::class ],
			[ 'val5', 'set', PropertyAccessError::class ],
		];
	}

	public function testGetAndSet() {
		$obj = new MyModelExample();

		$this->assertEquals(null, $obj->val1, 'Expect first null val for string');
		$this->assertEquals(0, $obj->val2, 'Expect first 0 number for int');
		$this->assertFalse($obj->val3, 'Expect first false val for bool');

		$obj->val1 = 'Some string';
		$obj->val2 = 12;
		$obj->val3 = true;
		$this->assertEquals('Some string', $obj->val1, 'Expecting changed value');
		$this->assertEquals(12, $obj->val2, 'Expecting changed value');
		$this->assertTrue($obj->val3, 'Expecting changed value');

		Settings::setSimpleObjectTypeCase(Settings::SO_SNAKE_CASE);

		$obj->val1 = 'test 2';
		$obj->val2 = 15;
		$obj->val3 = false;
		$this->assertEquals('test 2', $obj->val1, 'Expecting changed value for snake_case');
		$this->assertEquals(15, $obj->val2, 'Expecting changed value for snake_case');
		$this->assertFalse($obj->val3, 'Expecting changed value for snake_case');

		Settings::setSimpleObjectTypeCase(Settings::SO_CAMEL_CASE);
	}

	/**
	 * @param $field
	 * @param $type
	 * @param $exception_class
	 *
	 * @return void
	 * @runInSeparateProcess
	 * @dataProvider dataProviderExceptions
	 */
	public function testExceptions($field, $type, $exception_class) {
		$obj = new MyModelExample();

		if ($type == 'get') {
			$this->expectException($exception_class);
			$t = $obj->$field;
		} else if ($type == 'set') {
			$this->expectException($exception_class);
			$obj->$field = 'test';
		}
	}

}