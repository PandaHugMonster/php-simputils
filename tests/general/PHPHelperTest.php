<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\attributes\Property;
use spaf\simputils\Boolean;
use spaf\simputils\FS;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\Math;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\File;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\StackFifo;
use spaf\simputils\models\StackLifo;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\MetaMagic;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\prstr;

class MyObjectExample {
	use MetaMagic;
}

class MyDT extends DateTime {
	#[Property('date')]
	protected function getDateExt(): string {
		return "This is day: {$this->format('d')}, this is month: {$this->format('m')}, " .
			"this is year: {$this->format('Y')}";
	}
}

class MyDT2 {

}

class MyBoxConvertable extends SimpleObject {

	public $EVEN_some_MORE = null;

	public function toBox(bool $recursively = false, bool $with_class = false) {
		return new Box(['some' => 'stuff']);
	}

	public function toArray(
		bool $recursively = false,
		bool $with_class = false,
		array $exclude_fields = []
	): array {
		$res = [
			'EVEN_some_MORE' => 'EVEN stuff MORE',
		];

		if ($with_class) {
			$res[PHP::$serialized_class_key_name] = static::class;
		}

		return $res;
	}
}

class MyBoxConvertable2 extends SimpleObject {

	public $my_field = 'test';

}

/**
 * @covers \spaf\simputils\PHP
 * @covers \spaf\simputils\basic\pd
 * @covers \spaf\simputils\basic\bx
 * @covers \spaf\simputils\models\PhpInfo
 * @covers \spaf\simputils\generic\BasicInitConfig
 *
 * @uses \spaf\simputils\models\Version
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\traits\MetaMagic
 * @uses \spaf\simputils\generic\BasicVersionParser
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\FS
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\models\File
 * @uses \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\models\files\apps\DotEnvProcessor
 * @uses \spaf\simputils\models\files\apps\TextProcessor
 * @uses \spaf\simputils\models\files\apps\settings\DotEnvSettings
 * @uses \spaf\simputils\Str
 */
class PHPHelperTest extends TestCase {

	const THE_CAP = 'This is replacement for "die", so it could be tested and covered properly';

	/**
	 *
	 * FIX  Refactor
	 *
	 * @return void
	 * @runInSeparateProcess
	 */
//	public function testPleaseDie(): void {
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
//	}


	/**
	 *
	 * @return void
	 * @throws \Exception
	 * @uses \spaf\simputils\traits\SimpleObjectTrait
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser::parse()
	 * @runInSeparateProcess
	 * @uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\traits\MetaMagic
	 */
	public function testSerializationAndDeserialization() {
		$version_class = PHP::redef(Version::class);
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

		// FIX  TypeError : Cannot assign array to property spaf\simputils\models\Version::$_parser of type ?spaf\simputils\interfaces\VersionParserInterface
		//      src/models/Version.php:211
		//      src/traits/PropertiesTrait.php:221
		//      src/traits/PropertiesTrait.php:87
		//      src/traits/MetaMagic.php:538
		//      src/traits/MetaMagic.php:624
		//      src/traits/MetaMagic.php:565
		//      src/traits/MetaMagic.php:623
		//      src/PHP.php:241
		//      tests/general/PHPClassTest.php:148

		// $obj2 = PHP::deserialize($data1);
		// $this->assertInstanceOf($version_class, $obj2, 'Checking deserialization');

		// PHP Standard
		PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_PHP;

		$obj1 = new $version_class('1.2.3');

		$data1 = PHP::serialize($obj1);
		$this->assertIsString($data1, 'Is object serialized');

		$is_json = Str::isJson($data1);
		$this->assertFalse($is_json, 'Should not be json');

		$obj2 = PHP::deserialize($data1);
		$this->assertInstanceOf($version_class, $obj2, 'Checking deserialization');

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

		$res = PHP::deserialize('no json data here', enforced_type: PHP::SERIALIZATION_TYPE_JSON);
		$this->assertNull($res, 'Incorrect deserialization');

		PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_JSON;

		$res = PHP::serialize(new MyBoxConvertable());
//		pd('HERE ', $res);
		$res = PHP::deserialize($res);

		$this->assertEquals('EVEN stuff MORE', $res->EVEN_some_MORE);
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

	/**
	 * @return void
	 * @covers spaf\simputils\FS::lsFiles
	 * @covers spaf\simputils\FS::mkDir
	 * @covers spaf\simputils\FS::mkFile
	 * @covers spaf\simputils\FS::rmDir
	 * @covers spaf\simputils\FS::rmFile
	 * @throws \Exception
	 */
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
	 *
	 * @uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\traits\SimpleObjectTrait
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
	 * @uses \spaf\simputils\Boolean::from
	 *
	 * @uses \spaf\simputils\System
	 */
	public function testPhpInfo() {
		$php_info = PHP::info(true);
		$phpinfo_class = PHP::redef(PhpInfo::class);
		$this->assertInstanceOf($phpinfo_class, $php_info, 'PHP info is the object');
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
	 * @covers \spaf\simputils\Boolean::from
	 * @return void
	 * FIX  Should be moved out
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
	 * @covers \spaf\simputils\Boolean::from
	 * FIX  Should be moved out
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
		$phpinfo_class = PHP::redef(PhpInfo::class);
		$version_class = PHP::redef(Version::class);

		return [
			['this is string', 'string'],
			['anotherstringishere', 'string'],
			[12, 'integer'],
			[22.22, 'double'],
			[new $version_class('0.0.0', 'no app'), $version_class],
			[PHP::info(), $phpinfo_class],
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
		$box_class = PHP::redef(Box::class);
		$box = bx(['My array', 'data' => 'in my array']);
		$this->assertEquals($box_class, PHP::type($box));
	}

	/**
	 * @covers \spaf\simputils\traits\ArrayReadOnlyAccessTrait
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testArrayReadOnlyStuff() {
		$box_class = PHP::redef(Box::class);
		$phpi = PHP::info();
		$this->assertInstanceOf($box_class, $phpi->keys);

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
		$phpinfo_class = PHP::redef(PhpInfo::class);
		$version_class = PHP::redef(Version::class);

		$this->assertFalse(PHP::isClass('IaMnOtAcLaSs'));
		$this->assertTrue(PHP::isClass($phpinfo_class));

		$this->assertFalse(PHP::isClassIn($phpinfo_class, $version_class));
		$this->assertTrue(PHP::isClassIn(SimpleObject::class, $version_class));

		$obj1 = new $version_class('1.1.1');
		$cls1 = $version_class;

		$obj2 = new $version_class('1.1.2');

		$this->assertTrue(PHP::isClassIn($obj1, $cls1));

		$this->assertFalse(PHP::isClassIn($obj1, $cls1, true));
		$this->assertFalse(PHP::isClassIn($obj1, $cls1, true));

		$this->assertTrue(PHP::isClassIn($obj1, $obj2));
		$this->assertFalse(PHP::isClassIn($obj1, $obj2, true));
		$this->assertFalse(PHP::isClassIn($cls1, $obj1, true));

		$this->assertTrue(PHP::classContains($obj1, $obj2));

		$this->assertIsObject(PHP::createDummy($version_class));
		$this->assertIsObject(PHP::createDummy($obj1));

		$this->assertTrue(PHP::isArrayCompatible([]));
		$this->assertTrue(PHP::isArrayCompatible(bx([])));
		$this->assertFalse(PHP::isArrayCompatible($obj1));

	}

	/**
	 *
	 * @runInSeparateProcess
	 * @covers \spaf\simputils\models\DateTime::redefComponentName
	 * @covers \spaf\simputils\components\initblocks\DotEnvInitBlock
	 * @covers \spaf\simputils\special\CodeBlocksCacheIndex
	 * @uses \spaf\simputils\generic\BasicInitConfig
	 * @uses \spaf\simputils\basic\now
	 * @return void
	 */
	function testRedef() {

		$dt = now();
		$this->assertInstanceOf(DateTime::class, $dt);
		$this->assertNotInstanceOf(MyDT::class, $dt);

		PHP::init([
			'redefinitions' => [
				DateTime::redefComponentName() => MyDT::class
			]
		]);

		$dt = now();
		$this->assertInstanceOf(MyDT::class, $dt);

		$this->assertEquals(MyDT::class, PHP::redef(DateTime::class));
	}

	/**
	 * @covers \spaf\simputils\generic\BasicInitConfig
	 * @covers \spaf\simputils\special\CodeBlocksCacheIndex
	 * @runInSeparateProcess
	 * @return void
	 */
	function testRedefException3() {

		$this->expectException(Exception::class);

		PHP::init([
			'redefinitions' => [
				'test22' => MyDT::class
			]
		]);
	}

	/**
	 * @covers \spaf\simputils\special\CodeBlocksCacheIndex
	 * @runInSeparateProcess
	 * @return void
	 */
	function testRedefException4() {

		$this->expectException(Exception::class);

		PHP::init([
			'redefinitions' => [
				DateTime::redefComponentName() => MyDT::class
			]
		]);

		PHP::init([
			'redefinitions' => [
				DateTime::redefComponentName() => MyDT::class
			]
		]);
	}

	/**
	 *
	 * @covers \spaf\simputils\models\DateTime::redefComponentName
	 * @return void
	 */
	function testRedefException1() {
		$this->expectException(Exception::class);
		$this->assertEquals(MyDT::class, PHP::redef('just a nonsense string'));
	}

	/**
	 *
	 * @covers \spaf\simputils\models\DateTime::redefComponentName
	 * @return void
	 */
	function testRedefException2() {
		$this->expectException(Exception::class);
		$this->assertEquals(MyDT2::class, PHP::redef(MyDT2::class));
	}

	function testFileTransparentSupply() {
		$memory_file = FS::file();

		$this->assertInstanceOf(File::class, $memory_file);
		$b = FS::file($memory_file);

		$this->assertEquals($b->obj_id, $memory_file->obj_id);
	}

	function testBoxTransparentOrNullSupply() {
		$box = PHP::box();

		$this->assertInstanceOf(Box::class, $box);
		$b = PHP::box($box);

		$this->assertEquals($b->obj_id, $box->obj_id);
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testGetInitConfig() {
		$a = PHP::init();

		$b = PHP::getInitConfig();

		$this->assertInstanceOf(InitConfig::class, $a);
		$this->assertEquals($b, $a);
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testRedefPd() {
		$a = PHP::init([
			'redefinitions' => [
				InitConfig::REDEF_PD => function () {
					echo "my custom dying method.";
				}
			]
		]);

		$this->expectOutputString('my custom dying method.');

		pd('Some text that will be ignored');

	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testPrPd() {
		$str1 = 'text that will be printed out, but not dying';
		$str2 = 'TEST|-|TEST1|-|TEST2';
		$this->expectOutputString("{$str1}\n||HELLO {$str2} WORLD||\n");

		PHP::$allow_dying = false;

		pd($str1);

		PHP::init(new InitConfig([
			'redefinitions' => [
				InitConfig::REDEF_PR => function (...$args) {
					$args = new Box($args);
					echo "||HELLO {$args->join('|-|')} WORLD||\n";
				}
			]
		]));

		pr('TEST', 'TEST1', 'TEST2');

		$this->assertEquals("||HELLO te|-|st|-|wut WORLD||\n", prstr('te', 'st', 'wut'));
		$this->assertNull(prstr());
	}

	/**
	 * @uses \spaf\simputils\Boolean
	 * @uses \spaf\simputils\System
	 * @uses \spaf\simputils\attributes\PropertyBatch
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
	 * @uses \spaf\simputils\Math
	 * @uses \spaf\simputils\attributes\Extract::__construct
	 * @uses \spaf\simputils\models\StackLifo
	 * @uses \spaf\simputils\models\StackFifo
	 *
	 * @return void
	 */
	function testCommonalities() {
		$reflection = new ReflectionClass(PHP::class);
		$path = dirname($reflection->getFileName());

		$this->assertIsString(PHP::frameworkDir());
		$this->assertEquals($path, PHP::frameworkDir());

		$is_cli = Str::lower(PHP_SAPI) === 'cli';

		if ($is_cli) {
			$this->assertTrue(PHP::isCLI());
			$this->assertTrue(PHP::isConsole());
		} else {
			$this->assertFalse(PHP::isCLI());
			$this->assertFalse(PHP::isConsole());
		}

		// Converting to box object that does support custom toBox conversion
		$boxed_obj = PHP::box(new MyBoxConvertable());
		$this->assertInstanceOf(Box::class, $boxed_obj);
		$this->assertEquals(PHP::box(['some' => 'stuff']), $boxed_obj);

		// Generators to boxes
		$boxed_gen = PHP::box(Math::range(0, 9));
		$this->assertInstanceOf(Box::class, $boxed_gen);
		$this->assertEquals(PHP::box([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]), $boxed_gen);

		// Test Merger of box instance
		$merger = PHP::box([], $boxed_obj, $boxed_gen);
		$this->assertEquals(PHP::box([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'some' => 'stuff']), $merger);

		// Test Merger of mix instance
		$merger = PHP::box([], $boxed_obj, new MyBoxConvertable(), new MyBoxConvertable2());
		$this->assertEquals(PHP::box(['some' => 'stuff', 'my_field' => 'test']), $merger);
		$this->assertEmpty(PHP::env('TEST_VAR_IS_OK'));

		PHP::envSet('TEST_VAR_IS_OK', 'TrUe It iS');

		$this->assertNotEmpty(PHP::env('TEST_VAR_IS_OK'));
		$this->assertEquals('TrUe It iS', PHP::env('TEST_VAR_IS_OK'));

		$stack = PHP::stack(1, 2, 3, 4);
		$this->assertInstanceOf(StackLifo::class, $stack);

		$this->assertEquals(
			(array) PHP::box([1, 2, 3, 4]),
			(array) $stack
		);

		$this->assertEquals(4, $stack->pop());
		$this->assertEquals(3, $stack->pop());
		$this->assertEquals(2, $stack->size);

		$stack = PHP::stack(1, 2, 3, 4, type: 'fifo');
		$this->assertInstanceOf(StackFifo::class, $stack);

		$this->assertIsBool(PHP::info()->hasExtension('Core'));
		$this->assertFalse(PHP::info()->hasExtension('NonExistentExtension_blalbalba_lbalbbla'));

		$this->assertEquals('DateTime', PHP::classShortName(DateTime::class));
		$this->assertEquals('Str', PHP::classShortName(Str::class));
	}
}
