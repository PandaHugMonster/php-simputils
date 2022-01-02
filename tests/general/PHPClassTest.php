<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\Boolean;
use spaf\simputils\FS;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\models\Box;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\special\CodeBlocksCacheIndex;
use spaf\simputils\Str;
use spaf\simputils\traits\MetaMagic;
use function spaf\simputils\basic\box;

class MyObjectExample {
	use MetaMagic;
}

/**
 * @covers \spaf\simputils\PHP
 * @covers \spaf\simputils\basic\pd
 * @covers \spaf\simputils\basic\box
 * @covers \spaf\simputils\models\PhpInfo
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
	 *
	 * FIX  Refactor
	 *
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testPleaseDie(): void {
//		PHP::$allow_dying = false;
//
//		Settings::redefinePd(function ($data) {
//			print_r($data);
//			// do not put die, to do not corrupt tests
//		});
//
//		$str = 'This should be printed, is it?';
//		pd($str);
//		Settings::redefinePd(null);
//		pd($str);
//		$this->expectOutputString($str.$str."\n");
//		PHP::$allow_dying = true;
	}

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	public function testBoolStr(): void {
		$true = Str::from(true);
		$false = Str::from(false);
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
		$true = Str::isJson($json);
		$false = Str::isJson($json.'TTTT');
		$this->assertTrue($true, 'Check if json is correct');
		$this->assertFalse($false, 'Check if json is incorrect');
	}

	/**
	 *
	 * @depends testIsJsonString
	 * @return void
	 * @throws \Exception
	 *@uses \spaf\simputils\traits\SimpleObjectTrait
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser::parse()
	 * @runInSeparateProcess
	 * @uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\traits\MetaMagic
	 */
	public function testSerializationAndDeserialization() {
		$version_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);
		// JSON no meta-magic
		$obj1 = new stdClass();
		$obj1->option1 = 'test';

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = Str::isJson($data1);
		$this->assertTrue($is_json, 'By default should be json');

		$obj2 = PHP::deserialize($data1, stdClass::class);
		$this->assertInstanceOf(stdClass::class, $obj2, 'Checking deserialization');

		// JSON with meta-magic
		$obj1 = new $version_class('1.2.3');

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = Str::isJson($data1);
		$this->assertTrue($is_json, 'By default should be json');

		$obj2 = PHP::deserialize($data1);
		$this->assertInstanceOf($version_class::class, $obj2, 'Checking deserialization');

		// PHP Standard
		PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_PHP;

		$obj1 = new $version_class('1.2.3');

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = Str::isJson($data1);
		$this->assertFalse($is_json, 'Should not be json');

		$obj2 = PHP::deserialize($data1);
		$this->assertInstanceOf($version_class::class, $obj2, 'Checking deserialization');

		// stdClass version
		$obj1 = new stdClass();
		$obj1->option1 = 'test';

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = Str::isJson($data1);
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

		FS::rmFile($file_path);
	}

	public function testFilesRelatedFunctionality() {
		$dir = '/tmp/simputils/tests/test-files-related-functionality';
		$file = "{$dir}/my-very-file.txt";
		FS::mkDir($dir);
		$this->assertDirectoryExists($dir);

		$expected_content = 'Here is content';
		FS::mkFile($file, $expected_content);
		$this->assertFileExists($file);

//		$received_content = PHP::getFileContent($file);
//		$this->assertEquals($expected_content, $received_content);

		FS::rmFile($dir, true);

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
	 * @return void
	 *@uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\traits\SimpleObjectTrait
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
	 *
	 * @uses \spaf\simputils\System
	 */
	public function testPhpInfo() {
		$php_info = PHP::info(true);
		$phpinfo_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_PHP_INFO,
			PhpInfo::class
		);
		$this->assertInstanceOf($phpinfo_class::class, $php_info, 'PHP info is the object');
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
		$sub_res = Boolean::from($mixed_val);

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
		$sub_res = Boolean::from($mixed_val, true);
		$this->assertEquals(
			$expected_val,
			$sub_res,
			"Checking to bool STRICT conversion of {$mixed_val} to {$expected_val}"
		);
	}

	public function dataProviderType(): array {
		$phpinfo_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_PHP_INFO,
			PhpInfo::class
		);
		$version_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);
		return [
			['this is string', 'string'],
			['anotherstringishere', 'string'],
			[12, 'integer'],
			[22.22, 'double'],
			[new $version_class('0.0.0', 'no app'), $version_class::class],
			[PHP::info(), $phpinfo_class::class],
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
		$box_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_BOX,
			Box::class
		);
		$box = box(['My array', 'data' => 'in my array']);
		$this->assertEquals($box_class::class, PHP::type($box));
	}

	/**
	 * @covers \spaf\simputils\traits\ArrayReadOnlyAccessTrait
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testArrayReadOnlyStuff() {
		$box_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_BOX,
			Box::class
		);
		$phpi = PHP::info();
		$this->assertInstanceOf($box_class::class, $phpi->keys);

		$this->expectException(Exception::class);

		// This is done like that to avoid silly PHPStorm exceptions
		$name = 'kernel_name';
		$phpi->$name = 'test';
	}

	/**
	 * @return void
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
	 */
	public function testClassRelatedUtils() {
		$phpinfo_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_PHP_INFO,
			PhpInfo::class
		);
		$version_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);
		$this->assertFalse(PHP::isClass('IaMnOtAcLaSs'));
		$this->assertTrue(PHP::isClass($phpinfo_class::class));

		$this->assertFalse(PHP::isClassIn($phpinfo_class::class, $version_class::class));
		$this->assertTrue(PHP::isClassIn(SimpleObject::class, $version_class::class));

		$obj1 = new $version_class('1.1.1');
		$cls1 = $version_class::class;

		$obj2 = new $version_class('1.1.2');

		$this->assertTrue(PHP::isClassIn($obj1, $cls1));

		$this->assertFalse(PHP::isClassIn($obj1, $cls1, true));
		$this->assertFalse(PHP::isClassIn($obj1, $cls1, true));

		$this->assertTrue(PHP::isClassIn($obj1, $obj2));
		$this->assertFalse(PHP::isClassIn($obj1, $obj2, true));
		$this->assertFalse(PHP::isClassIn($cls1, $obj1, true));

		$this->assertTrue(PHP::classContains($obj1, $obj2));

		$this->assertIsObject(PHP::createDummy($version_class::class));
		$this->assertIsObject(PHP::createDummy($obj1));

		$this->assertTrue(PHP::isArrayCompatible([]));
		$this->assertTrue(PHP::isArrayCompatible(box([])));
		$this->assertFalse(PHP::isArrayCompatible($obj1));

	}
}
