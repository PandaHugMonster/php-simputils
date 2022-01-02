<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;


/**
 * @covers \spaf\simputils\generic\SimpleObject
 * @covers \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\PHP
 */
class SimpleObjectTest extends TestCase {

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testBasics() {
		$a = new class extends SimpleObject {

		};
		$this->assertIsInt($a->obj_id);
		$this->assertEquals(PHP::type($a), $a->obj_type);
		$this->assertIsString($a->class_short);

		$this->assertIsString("This is an object string ref: {$a}");
		SimpleObject::$to_string_format_json = true;
		$this->assertJson(strval($a));
		SimpleObject::$to_string_format_json = true;
	}
}
