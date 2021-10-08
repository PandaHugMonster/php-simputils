<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\Settings;
use spaf\simputils\traits\MetaMagic;
use function spaf\simputils\basic\pd;

class MyObjectExample {
	use MetaMagic;
}

/**
 * @covers \spaf\simputils\PHP
 * @covers \spaf\simputils\basic\pd
 * @uses \spaf\simputils\Settings
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

}