<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Extract;
use spaf\simputils\attributes\Property;
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\components\normalizers\LowerCaseNormalizer;
use spaf\simputils\exceptions\PropertyDoesNotExist;
use spaf\simputils\exceptions\PropertyIsWriteOnly;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\traits\ArrayReadOnlyAccessTrait;

/**
 *
 * @property ?DateTime $field1
 * @property string $field2
 * @property string $field3
 * @property mixed $field4
 * @property mixed $field5
 * @property mixed $field6
 */
class PreAOne extends SimpleObject {

	protected $field0 = 0;

	#[Property]
	protected ?DateTime $_field1 = null;

	#[Property(valid: LowerCaseNormalizer::class)]
	protected string $_field2 = 'TEstOVayA StriNG WOW!';

	#[Property(valid: 'upper')]
	protected string $_field3 = '3';

	#[Property]
	protected $_field4 = 4;

	#[Property]
	protected $_field5 = 5;

	#[Property]
	protected $_field6 = 6;
}

#[DebugHide]
class PreATwo extends PreAOne {

}

#[DebugHide(false, 'bebebe')]
class PreAThree extends PreAOne {

}

/**
 *
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

	#[Extract(false)]
	#[Property]
	protected $_test_sto_piatsot = '100 500...';

	private $test1 = 'test1';

	protected $test2 = 'test2';

	#[DebugHide]
	private $_test7 = 'test7';

	#[PropertyBatch]
	private $props1 = ['test3', 'test4'];

	#[PropertyBatch]
	private $props2 = ['test5', 'test6'];

	#[Extract(false)]
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

	#[DebugHide]
	public $_test8 = null;

	#[Property('test8')]
	public function setTest8($var) {
		$this->_test8 = $var;
	}

	#[Extract(false)]
	#[DebugHide]
	private $_test9 = 'test9';

	#[Extract(false)]
	#[DebugHide]
	#[Property('test9')]
	public function getTest9(): null|string|Box {
		return $this->_test9;
	}

	#[Property('test99')]
	public function getTest99(): bool|Box|string {
		return true;
	}

	#[Extract(false)]
	#[DebugHide]
	public $_duo1 = null;

	#[DebugHide(false)]
	#[Property('duo1')]
	public function duo1($var, $type): ?string {
		if ($type === 'get') {
			return $this->_duo1;
		}
		$this->_duo1 = $var;
		return null;
	}

	#[Property('tort', type: 'tort')]
	public function tort(): mixed {
		return 'tort';
	}
}

/**
 *
 * @property mixed $var1
 * @property mixed $var2
 * @property mixed $var3
 * @property mixed $var4
 */
class B extends Box {

	public $_prop1 = 'cacao';

	#[Property('prop1')]
	protected function setProp1($var) {
		$this->_prop1 = $var;
	}

	#[PropertyBatch(storage: PropertyBatch::STORAGE_SELF)]
	protected function fields() {
		return ['var1', 'var2', 'var3' => 'default value 1', 'var4' => 'default value 2'];
	}

	#[PropertyBatch(type: 'get')]
	protected $fields2 = [
		'var100',
		'var200',
		'var300' => 'get 300',
		'var400' => 'get 400',
	];

	#[PropertyBatch(type: 'get')]
	protected $fields2_2 = [
		'var1000' => 'get 1000',
	];

	#[PropertyBatch(type: 'set')]
	protected $fields3 = [
		'var100',
		'var200',
		'var300' => 'set 300',
		'var400' => 'set 400',
	];

	#[PropertyBatch(type: 'set')]
	protected $fields3_3 = [
		'var2000' => 'set 2000',
	];

	#[Property('var0001', type: 'get')]
	protected function getVar0001() {
		return '666';
	}

	public $_var0011 = 'none';

	#[Property('var0011', type: 'set')]
	protected function getVar0011($var) {
		$this->_var0011 = $var;
	}

	protected $_var0010 = '666-999';

	#[Property('var0010', type: 'both')]
	protected function getVar0010($var, $type): ?string {
		if ($type === 'get') {
			return $this->_var0010;
		}

		$this->_var0010 = $var;
		return null;
	}
}
class C extends B {
	use ArrayReadOnlyAccessTrait;
}

/**
 *
 * @property mixed $simple_one
 * @property ?\spaf\simputils\models\DateTime $simple_two
 */
class D extends SimpleObject {

	#[Property]
	protected $_simple_one = null;

	#[Property('simple_two')]
	protected ?DateTime $_simple_two = null;
}

/**
 * Properties test class
 *
 * @covers \spaf\simputils\attributes\PropertyBatch
 * @covers \spaf\simputils\attributes\Property
 * @covers \spaf\simputils\traits\PropertiesTrait
 *
 * @uses \spaf\simputils\generic\SimpleObject
 * @uses \spaf\simputils\traits\MetaMagic::_ilpRegisterObjIdUsage
 * @uses \spaf\simputils\traits\MetaMagic::_metaMagic
 * @uses \spaf\simputils\traits\MetaMagic::_toArray
 * @uses \spaf\simputils\traits\MetaMagic::toArray
 */
class PropertiesTest extends TestCase {

	/**
	 * @uses \spaf\simputils\Str
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
			$this->assertNotEmpty($obj->{'test'.$i});

			$i = 2;
			$this->assertEquals('test'.$i, $obj->{'test'.$i});
			$this->assertNotEmpty($obj->{'test'.$i});

			$i = 7;
			$this->assertEquals('test'.$i, $obj->{'test'.$i});
			$this->assertNotEmpty($obj->{'test'.$i});

			$i = 8;
			$this->assertNull($obj->{'_test'.$i});
			$obj->{'test'.$i} = 'TEST8';
			$this->assertEquals('TEST8', $obj->{'_test'.$i});
			$this->assertNotEmpty($obj->{'_test'.$i});

			$i = 98;
			$this->assertEquals(false, isset($obj->{'test'.$i}));
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

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testDifferentCoverageChecks() {
		$a1 = new A();

		$this->assertTrue(isset($a1->test99));

		$this->assertTrue(isset($a1->test99));

		$this->assertTrue(isset($a1->duo1));

		$this->assertFalse(isset($a1->tort));

		$b0 = new B();

		$this->assertEquals('get 300', $b0->var300);
		$this->assertTrue(isset($b0->var1000));


		$b = new B();

		$this->assertTrue(isset($b->var1));

		$b->var1 = 'New 1';
		$b->var2 = 2;
		$b->var3 = true;
		$b->var4 = 10.10;

		$this->assertEquals('New 1', $b->var1);
		$this->assertEquals(2, $b->var2);
		$this->assertTrue($b->var3);
		$this->assertEquals(10.10, $b->var4);

		$this->assertFalse(isset($b->var100));

		$b->var100 = 'New 1';

		$this->assertTrue(isset($b->var100));

		$b->var200 = 2;
		$b->var300 = true;
		$b->var400 = 10.10;

		$this->assertEquals('New 1', $b->var100);
		$this->assertEquals(2, $b->var200);
		$this->assertTrue($b->var300);
		$this->assertEquals(10.10, $b->var400);

		$this->assertEquals('666', $b->var0001);
		$this->assertEquals('666-999', $b->var0010);

		$b->var0011 = 'toot';
		$this->assertEquals('toot', $b->_var0011);

		$dd = new D();
		$this->assertNull($dd->simple_one);
		$this->assertNull($dd->simple_two);

		$dd->simple_one = 'just a text data';
		$this->assertIsString($dd->simple_one);
		$this->assertEquals('just a text data', $dd->simple_one);

		$dd->simple_two = '2020-02-05';
		$this->assertInstanceOf(DateTime::class, $dd->simple_two);
		$this->assertEquals('2020#02', $dd->simple_two->format('Y#m'));
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testExceptionOnReadOnlyAccess() {
		$c = new C();

		$this->expectException(Exception::class);
		$c->var1 = 'New 1';
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testExceptionOnReadOnlyField() {
		$b0 = new B();

		$this->expectException(PropertyIsWriteOnly::class);
		$this->assertEquals('get 300', $b0->var2000);
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testExceptionOnPropertyDoesNotExist() {
		$b0 = new B();

		$this->expectException(PropertyDoesNotExist::class);
		$this->assertEquals('get 300', $b0->tort);
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testExceptionOnPropertyOnlySet() {
		$b0 = new B();

		$this->expectException(PropertyIsWriteOnly::class);
		$this->assertEquals(null, $b0->prop1);
	}

	/**
	 *
	 * @uses \spaf\simputils\models\Box
	 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\PHP
	 *
	 * @return void
	 * @throws \spaf\simputils\exceptions\InfiniteLoopPreventionExceptions
	 */
	function testExtractFieldsAndDebugOutput() {
		$a = new A();
		$a->test3 = 666;
		$this->assertEquals([
			'duo1' => '****',
			'kk_0' => null,
			'kk_1' => null,
			'kk_2' => null,
			'kk_3' => null,
			'kk_4' => null,
			'kk_5' => null,
			'test1' => 'test1',
			'test2' => 'test2',
			'test3' => 666,
			'test4' => null,
			'test5' => null,
			'test6' => null,
			'test7' => 'test7',
			'test99' => true,
			'test_sto_piatsot' => '100 500...',
		], $a->__debugInfo());
		$this->assertEquals([
			'duo1' => null,
			'test1' => 'test1',
			'test2' => 'test2',
			'test3' => 666,
			'test4' => null,
			'test5' => null,
			'test6' => null,
			'test7' => 'test7',
			'test99' => true,
			'_test7' => 'test7',
			'_test8' => null,
		], $a->toArray());

		$pa1 = new PreAOne();
		$kk = [
			'field0' => 0,
			'field1' => null,
			'field2' => 'TEstOVayA StriNG WOW!',
			'field3' => 3,
			'field4' => 4,
			'field5' => 5,
			'field6' => 6,
		];
		$this->assertEquals($kk, $pa1->toArray());
		$this->assertEquals($kk, $pa1->__debugInfo());

		$pa2 = new PreATwo();
		$this->assertEmpty($pa2->__debugInfo());

		$pa3 = new PreAThree();
		$this->assertNotEmpty($v = $pa3->__debugInfo());
		$this->assertEquals(['bebebe'], $v);
	}

	/**
	 * @uses \spaf\simputils\DT
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\components\normalizers\DateTimeNormalizer
	 * @uses \spaf\simputils\components\normalizers\LowerCaseNormalizer
	 * @uses \spaf\simputils\components\normalizers\UpperCaseNormalizer
	 * @uses \spaf\simputils\components\normalizers\BooleanNormalizer
	 * @uses \spaf\simputils\models\DateTime
	 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
	 *
	 * @return void
	 */
	function testValidatorsAspects() {
		$pa1 = new PreAOne();
		$pa1->field1 = 42;
		$this->assertInstanceOf(DateTime::class, $pa1->field1);

		$this->assertEquals('TEstOVayA StriNG WOW!', $pa1->field2);
		$pa1->field2 = "BIGGER_THAN_IT_LOOKS :: {$pa1->field2}";
		$this->assertEquals(
			'bigger_than_it_looks :: testovaya string wow!',
			$pa1->field2
		);

		$pa1->field3 = "new string that suppose to be lower case";
		$this->assertEquals(
			'NEW STRING THAT SUPPOSE TO BE LOWER CASE',
			$pa1->field3
		);
	}
}
