<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\PHP;
use spaf\simputils\traits\MetaMagic;

class MyObject {
	use MetaMagic;

	public function __construct(
		public ?string $field_1 = null,
		public ?int $field_2 = null,
		public ?bool $field_3 = null,
	) {

	}
}

/**
 * @covers \spaf\simputils\traits\MetaMagic
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 */
class MetaMagicTest extends TestCase {

	public function testJson() {
		$json_string = '{"field_1": "test 1", "'
			.PHP::$serialized_class_key_name
			.'": "'.MyObject::class.'"}';
		$obj = MyObject::fromJson($json_string);
		$this->assertInstanceOf(MyObject::class, $obj, 'Is correct class used');
		$this->assertEquals('test 1', $obj->field_1, 'Is correct value in a field');

		$obj->field_2 = 22;

		$res = $obj->toJson();
		$this->assertIsString($res, 'Is a string');

		$json = json_decode($res, true);

		$this->assertIsArray($json, 'Is a json a proper array');
		$this->assertEquals($obj->field_2, $json['field_2'], 'Is a proper value');
		$this->assertArrayNotHasKey(PHP::$serialized_class_key_name, $json, 'Missing class key in a json array');

		$res = $obj->toJson(true, true);
		$this->assertIsString($res, 'Is a pretty string');

		$res = $obj->toJson(with_class: true);
		$this->assertIsString($res, 'Is a string');

		$json = json_decode($res, true);

		$this->assertArrayHasKey(PHP::$serialized_class_key_name, $json, 'Has a proper class key in a json array');
		$this->assertEquals($obj::class, $json[PHP::$serialized_class_key_name], 'Is a correct class in a serialization field');
	}

	public function testSerializationAndDeserialization() {
		$obj = new MyObject('test 100500');

		// JSON
		$str = PHP::serialize($obj);

		$this->assertIsString($str, 'Check if serialized');

		$new_obj = PHP::deserialize($str);

		$this->assertInstanceOf(MyObject::class, $new_obj, 'Is correct deserialization (json)');
		$this->assertEquals($obj->field_1, $new_obj->field_1, 'Is deserialization field is not empty (json)');

		// PHP standard serialization/deserialization
		PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_PHP;

		$str = PHP::serialize($obj);

		$this->assertIsString($str, 'Check if serialized');

		$new_obj = PHP::deserialize($str);

		$this->assertInstanceOf(MyObject::class, $new_obj, 'Is correct deserialization (php standard)');
		$this->assertEquals($obj->field_1, $new_obj->field_1, 'Is deserialization field is not empty (php standard)');

		PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_JSON;
	}
}
