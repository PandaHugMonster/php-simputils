<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\models\Box;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\Settings;
use spaf\simputils\traits\MetaMagic;
use function spaf\simputils\basic\box;
use function spaf\simputils\basic\pd;

class MyObjectExample {
	use MetaMagic;
}

/**
 * @covers \spaf\simputils\PHP
 * @covers \spaf\simputils\basic\pd
 * @covers \spaf\simputils\basic\box
 * @covers \spaf\simputils\models\PhpInfo
 * @uses \spaf\simputils\Settings
 * @uses \spaf\simputils\models\Version
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\traits\MetaMagic
 * @uses \spaf\simputils\generic\BasicVersionParser
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\models\Box
 */
class PHPClassTest extends TestCase {

	const THE_CAP = 'This is replacement for "die", so it could be tested and covered properly';

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testPleaseDie(): void {
		PHP::$allow_dying = false;

		Settings::redefinePd(function ($data) {
			print_r($data);
			// do not put die, to do not corrupt tests
		});

		$str = 'This should be printed, is it?';
		pd($str);
		Settings::redefinePd(null);
		pd($str);
		$this->expectOutputString($str.$str."\n");
		PHP::$allow_dying = true;
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testBoolStr(): void {
		$true = PHP::boolStr(true);
		$false = PHP::boolStr(false);
		$this->assertEquals('true', $true, 'Check if true is true');
		$this->assertEquals('false', $false, 'Check if false is false');
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testIsJsonString(): void {
		$json = json_encode([
			'my_field' => 'Some Value',
			'int_value' => 12,
			'boolval' => false,
		]);
		$true = PHP::isJsonString($json);
		$false = PHP::isJsonString($json.'TTTT');
		$this->assertTrue($true, 'Check if json is correct');
		$this->assertFalse($false, 'Check if json is incorrect');
	}

	/**
	 *
	 * @depends testIsJsonString
	 * @uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\traits\MetaMagic
	 * @uses \spaf\simputils\traits\SimpleObjectTrait
	 * @uses \spaf\simputils\versions\DefaultVersionParser::parse()
	 * @runInSeparateProcess
	 * @return void
	 * @throws \Exception
	 */
	public function testSerializationAndDeserialization() {
		// JSON no meta-magic
		$obj1 = new stdClass();
		$obj1->option1 = 'test';

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = PHP::isJsonString($data1);
		$this->assertTrue($is_json, 'By default should be json');

		$obj2 = PHP::deserialize($data1, stdClass::class);
		$this->assertInstanceOf(stdClass::class, $obj2, 'Checking deserialization');

		// JSON with meta-magic
		$obj1 = new Version('1.2.3');

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = PHP::isJsonString($data1);
		$this->assertTrue($is_json, 'By default should be json');

		$obj2 = PHP::deserialize($data1);
		$this->assertInstanceOf(Version::class, $obj2, 'Checking deserialization');

		// PHP Standard
		PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_PHP;

		$obj1 = new Version('1.2.3');

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = PHP::isJsonString($data1);
		$this->assertFalse($is_json, 'Should not be json');

		$obj2 = PHP::deserialize($data1);
		$this->assertInstanceOf(Version::class, $obj2, 'Checking deserialization');

		// stdClass version
		$obj1 = new stdClass();
		$obj1->option1 = 'test';

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = PHP::isJsonString($data1);
		$this->assertFalse($is_json, 'Should not be json');

		$obj2 = PHP::deserialize($data1, stdClass::class);
		$this->assertInstanceOf(stdClass::class, $obj2, 'Checking deserialization');


		PHP::$serialization_mechanism = 'FAKED';
		$res = PHP::deserialize($data1);
		$this->assertNull($res, 'Incorrect deserialization because of wrong mechanism');

		$res = PHP::serialize($obj1);
		$this->assertNull($res, 'Incorrect serialization because of wrong mechanism');

		$res = PHP::deserialize(null);
		$this->assertNull($res, 'Incorrect deserialization');

		PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_JSON;
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 * @throws \Exception
	 */
	public function testSerializationException() {
		$fd_resource = tmpfile();
		$meta_data = stream_get_meta_data($fd_resource);
		$file_path = $meta_data['uri'];
		fwrite($fd_resource, 'Some data for a resource');

		$this->expectException(Exception::class);
		PHP::serialize($fd_resource);
		fclose($fd_resource);

		PHP::rmFile($file_path);
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 * @throws \ReflectionException
	 */
	public function testDeserializationException() {
		$this->expectException(Exception::class);
		PHP::deserialize('??');
	}

	public function testFilesRelatedFunctionality() {
		$dir = '/tmp/simputils/tests/test-files-related-functionality';
		$file = "{$dir}/my-very-file.txt";
		PHP::mkDir($dir);
		$this->assertDirectoryExists($dir);

		$expected_content = 'Here is content';
		PHP::mkFile($file, $expected_content);
		$this->assertFileExists($file);

		$received_content = PHP::getFileContent($file);
		$this->assertEquals($expected_content, $received_content);

		PHP::rmFile($dir, true);

		$this->assertDirectoryDoesNotExist($dir);
	}

	public function testDirectTraitUsageCheck() {
		$obj = new MyObjectExample();
		$res = PHP::classUsesTrait($obj, MetaMagic::class);
		$this->assertTrue($res, 'Is directly used meta-magic');
	}

	/**
	 *
	 * @covers \spaf\simputils\models\PhpInfo
	 * @uses \spaf\simputils\helpers\SystemHelper
	 * @uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\traits\SimpleObjectTrait
	 * @uses \spaf\simputils\versions\DefaultVersionParser
	 *
	 * @return void
	 */
	public function testPhpInfo() {
		$php_info = PHP::info(true);
		$this->assertInstanceOf(PhpInfo::class, $php_info, 'PHP info is the object');
		$this->assertNotEmpty($php_info, 'PHP info is not empty');

		$expected_keys = array_keys($php_info->toArray());
		foreach ($expected_keys as $key)
			$this->assertArrayHasKey($key, $php_info, 'Does have '.$key);
	}

	/**
	 * @return array[]
	 */
	public function dataProviderToBool(): array {
		return [
			['true', true], ['1', true], ['T', true], ['trUe', true], ['t', true], ['y', true],
			['yes', true], ['yEs', true], ['enabled', true], [1, true], ['+', true],

			['false', false], ['0', false], [0, false], [null, false], ['F', false],
			['FalsE', false], ['f', false], ['n', false], ['no', false], ['No', false],
			['disabled', false], ['-', false],

			['coocoo', null], ['dodo', null], ['bee', null], ['gogo', null],
		];
	}

	/**
	 * @param mixed $mixed_val    Mixed value from dp
	 * @param bool  $expected_val Expected value from dp
	 *
	 * @dataProvider dataProviderToBool
	 * @return void
	 */
	public function testAsBool(mixed $mixed_val, ?bool $expected_val) {
		$sub_res = PHP::asBool($mixed_val);

		// Due to dataProviderToBool works for both strict and non strict, adjusting null
		$expected_val = $expected_val === null?false:$expected_val;

		$this->assertEquals(
			$expected_val,
			$sub_res,
			"Checking to bool non strict conversion of {$mixed_val} to {$expected_val}"
		);
	}

	/**
	 * @param mixed $mixed_val    Mixed value from dp
	 * @param bool  $expected_val Expected value from dp
	 *
	 * @dataProvider dataProviderToBool
	 * @return void
	 */
	public function testAsBoolStrict(mixed $mixed_val, ?bool $expected_val) {
		$sub_res = PHP::asBool($mixed_val, true);
		$this->assertEquals(
			$expected_val,
			$sub_res,
			"Checking to bool STRICT conversion of {$mixed_val} to {$expected_val}"
		);
	}

	public function dataProviderType(): array {
		return [
			['this is string', 'string'],
			['anotherstringishere', 'string'],
			[12, 'integer'],
			[22.22, 'double'],
			[new Version('0.0.0', 'no app'), Version::class],
			[PHP::info(), PhpInfo::class],
			[true, 'boolean'],
			[false, 'boolean'],
		];
	}

	/**
	 * @param $in
	 * @param $expected
	 *
	 * @dataProvider dataProviderType
	 * @return void
	 */
	public function testType($in, $expected) {
		$res = PHP::type($in);
		$this->assertEquals($expected, $res, "Is {$in} of type {$expected}");
	}

	public function testBox() {
		$box = box(['My array', 'data' => 'in my array']);
		$this->assertEquals(Box::class, PHP::type($box));
	}

	/**
	 * @covers \spaf\simputils\traits\ArrayReadOnlyAccessTrait
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testArrayReadOnlyStuff() {
		$phpi = PHP::info();
		$this->assertInstanceOf(Box::class, $phpi->keys);

		$this->expectException(Exception::class);

		// This is done like that to avoid silly PHPStorm exceptions
		$name = 'kernel_name';
		$phpi->$name = 'test';
	}

	/**
	 * @uses \spaf\simputils\versions\DefaultVersionParser
	 * @return void
	 */
	public function testClassRelatedUtils() {
		$this->assertFalse(PHP::isClass('IaMnOtAcLaSs'));
		$this->assertTrue(PHP::isClass(PhpInfo::class));

		$this->assertFalse(PHP::isClassIn(PhpInfo::class, Version::class));
		$this->assertTrue(PHP::isClassIn(SimpleObject::class, Version::class));

		$obj1 = new Version('1.1.1');
		$cls1 = Version::class;

		$obj2 = new Version('1.1.2');

		$this->assertTrue(PHP::isClassIn($obj1, $cls1));

		$this->assertFalse(PHP::isClassIn($obj1, $cls1, true));
		$this->assertFalse(PHP::isClassIn($obj1, $cls1, true));

		$this->assertTrue(PHP::isClassIn($obj1, $obj2));
		$this->assertFalse(PHP::isClassIn($obj1, $obj2, true));
		$this->assertFalse(PHP::isClassIn($cls1, $obj1, true));

		$this->assertTrue(PHP::classContains($obj1, $obj2));

		$this->assertIsObject(PHP::createDummy(Version::class));
		$this->assertIsObject(PHP::createDummy($obj1));

		$this->assertTrue(PHP::isArrayCompatible([]));
		$this->assertTrue(PHP::isArrayCompatible(box([])));
		$this->assertFalse(PHP::isArrayCompatible($obj1));

	}
}
