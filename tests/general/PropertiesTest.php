<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\attributes\Property;
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\generic\SimpleObject;

/**
 * @property $test1
 * @property $test2
 * @property $test3
 * @property $test4
 * @property $test5
 * @property $test6
 * @property $test7
 *
 * @property-write $test8
 * @property-read $test9
 *
 * @property $kk_0
 * @property $kk_1
 * @property $kk_2
 * @property $kk_3
 * @property $kk_4
 * @property $kk_5
 */
class A extends SimpleObject {

	private $test1 = 'test1';
	private $test2 = 'test2';
	private $_test7 = 'test7';

	#[PropertyBatch]
	private $props1 = ['test3', 'test4'];

	#[PropertyBatch]
	private $props2 = ['test5', 'test6'];

	#[PropertyBatch]
	private function getDynamicListOfProperties() {
		$res = [];
		for ($i = 0; $i <= 5; $i++) {
			$res[] = 'kk_'.$i;
		}
		return $res;
	}

	#[Property('test1')]
	public function getTest1(): ?string {
		return $this->test1;
	}

	#[Property('test1')]
	public function setTest1($var) {
		$this->test1 = $var;
	}

	#[Property('test2')]
	public function getTest2(): ?string {
		return $this->test2;
	}

	#[Property('test2')]
	public function setTest2($var) {
		$this->test2 = $var;
	}

	#[Property]
	public function test7($var, $type): ?string {
		if ($type === Property::TYPE_GET) {
			return $this->_test7;
		}

		$this->_test7 = $var;
		return null;
	}

	public $_test8 = null;

	#[Property('test8')]
	public function setTest8($var) {
		$this->_test8 = $var;
	}

	private $_test9 = 'test9';

	#[Property('test9')]
	public function getTest9(): ?string {
		return $this->_test9;
	}
}

/**
 * @covers \spaf\simputils\attributes\PropertyBatch
 * @covers \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\generic\SimpleObject
 */
class PropertiesTest extends TestCase {

	/**
	 * FIX  Unfinished implementation of the default values for PropertyBatch
	 * @return void
	 */
	public function testValuesCheckProperty() {
		$size = 100;
		$arr = [];
		for ($i = 0; $i < $size; $i++) {
			$obj = new A();
			$arr[$i] = $obj;
		}

		foreach ($arr as $obj) {
			$i = 1;
			$this->assertEquals('test'.$i, $obj->{'test'.$i});
			$i = 2;
			$this->assertEquals('test'.$i, $obj->{'test'.$i});
			$i = 7;
			$this->assertEquals('test'.$i, $obj->{'test'.$i});
			$i = 8;
			$this->assertNull($obj->{'_test'.$i});
			$obj->{'test'.$i} = 'TEST8';
			$this->assertEquals('TEST8', $obj->{'_test'.$i});
		}

		foreach ($arr as $i => $obj) {
			for ($k = 1; $k <= 7; $k++) {
				$obj->{'test'.$k} = "{$i}_{$k}";
			}

			for ($k = 0; $k <= 5; $k++) {
				$obj->{'kk_'.$k} = "kk-{$i}_{$k}";
			}
		}
		foreach ($arr as $i => $obj) {
			for ($k = 1; $k <= 7; $k++) {
				$this->assertEquals("{$i}_{$k}", $obj->{'test'.$k});
			}
			for ($k = 0; $k <= 5; $k++) {
				$this->assertEquals("kk-{$i}_{$k}", $obj->{'kk_'.$k});
			}
		}
	}
}
