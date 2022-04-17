<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
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
 *
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\components\normalizers\BooleanNormalizer
 *
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

	function testOther() {
		$obj1 = new MyObject('test 100500');
		$obj2 = PHP::box(['one', 'two', 'three']);

		$tb = $obj1->toBox(true);
		$this->assertInstanceOf(Box::class, $tb);

		$tb = $obj2->toBox(true, true);
		$this->assertInstanceOf(Box::class, $tb);

//		$nobj = new MyObject;
//		MyObject::_metaMagic($nobj, '___setup', $obj1->toArray(with_class: true));
//
//		$json = $tb->toJson(with_class: true);
//		$this->assertIsString($json);
//		$this->assertEquals(
//			'{"0":"one","1":"two","2":"three","#class":"spaf\\\\simputils\\\\models\\\\Box"}',
//			$json
//		);
//
//		$res = $tb->toArray(with_class: false);
//
//		$new_box_from_json = PHP::box();
//		Box::_metaMagic($new_box_from_json, '___setup', $res);
//		$this->assertInstanceOf(Box::class, $new_box_from_json);
//		$this->assertEquals(PHP::box(['one', 'two', 'three']), $new_box_from_json);
	}
}
