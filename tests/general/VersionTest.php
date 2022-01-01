<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\exceptions\IncorrectVersionFormat;
use spaf\simputils\generic\BasicVersionParser;
use spaf\simputils\models\Version;
use spaf\simputils\Str;
use spaf\simputils\versions\DefaultVersionParser;

class CustomParserSample extends DefaultVersionParser {

	public function parse(Version $version_object, ?string $string_version): array {
		return [
			'major' => 100500,
			'minor' => 0,
			'patch' => 100501,
			'prefix' => null,
			'postfix' => null,
			'build_type' => 'TEST',
			'build_revision' => 100502,
		];
	}
}

/**
 * @todo Add more tests
 *
 * @covers \spaf\simputils\models\Version
 * @covers \spaf\simputils\versions\DefaultVersionParser
 * @covers \spaf\simputils\exceptions\IncorrectVersionFormat
 * @covers \spaf\simputils\generic\BasicVersionParser
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Settings
 * @uses \spaf\simputils\traits\MetaMagic
 * @uses \spaf\simputils\interfaces\VersionParserInterface
 * @uses \spaf\simputils\traits\PropertiesTrait
 */
class VersionTest extends TestCase {

	const APP_NAME = 'App Doe';

	public function dataProviderCommon(): array {
		return [
			['0.1.2'],
			['100.500.0A99'],
			['20090130'],
		];
	}

	public function dataProviderFirst(): array {
		return [
			[ '0.1.2', '100.500.0A99', '20090130' ],
		];
	}

	public function dataProviderSecond(): array {
		return [
			// v1 <= v2 < v3 == v4 != v5
			[ '0.1.0', '00.11.00', '01.02.03', '1.2.3', '1.2.3-A1', ],
			[ '1.2.3', '1.2.4', '1.3.0', '1.3', '1.3.0-A2', ],
			[ '1.2.3', '5.2.4', '6.0.0', '6', '6.0.0-a3', ],
			[ '1.2.3A2', '1.2.3-A3', '6.0.0RC1', '6.0.0-RC1', '6.0.0a55' ],
			[ '1.1.1A2', '1.1.1-A3', '3.0.0-rc666', '3.0.0RC666', '3.0.0' ],
			[ '1.0.0', '1.0.0-A1', '1.0.1', '1.00.01', '0.11.10' ],
			[ '1.0.0-A', '1.0.0-b', '1.0.1', '1.00.01', '0.11.10' ],
		];
	}

	public function dataProviderForDebugInfo(): array {
		return [
			['55.15.2'],
			['2.2.3'],
			['10'],
		];
	}

	/**
	 *
	 * @dataProvider dataProviderFirst
	 *
	 * @param $str_v1
	 * @param $str_v2
	 * @param $str_v3
	 *
	 * @return void
	 */
	public function testVersionObjectCreationAndParsing($str_v1, $str_v2, $str_v3): void {
		$v1 = new Version($str_v1);
		$this->assertInstanceOf(Version::class, $v1, 'Checking non-empty object creation');

		$this->assertEquals(0, $v1->major, 'Major version value check');
		$this->assertEquals(1, $v1->minor, 'Minor version value check');
		$this->assertEquals(2, $v1->patch, 'Patch version value check');

		$v2 = new Version($str_v2, static::APP_NAME);
		$this->assertEquals('A', $v2->build_type, 'Check build type');
		$this->assertEquals(99, $v2->build_revision, 'Check build revision');

		$v3 = new Version($str_v3);
		$this->assertEquals(intval($str_v3), $v3->major, 'Another major version value check');
		$this->assertEquals(0, $v3->minor, 'Another minor version value check');
		$this->assertEquals(0, $v3->patch, 'Another patch version value check');

	}

	/**
	 *
	 * @dataProvider dataProviderSecond
	 *
	 * @param $str_v1
	 * @param $str_v2
	 * @param $str_v3
	 * @param $str_v4
	 * @param $str_v5
	 *
	 * @return void
	 */
	public function testComparisonOfVersions($str_v1, $str_v2, $str_v3, $str_v4, $str_v5): void {

		$v1 = new Version($str_v1, static::APP_NAME);
		$v2 = new Version($str_v2, static::APP_NAME);
		$v3 = new Version($str_v3, static::APP_NAME);
		$v4 = new Version($str_v4, static::APP_NAME);
		$v5 = new Version($str_v5, static::APP_NAME);


		// $v1 vs $v2
		$r = $v1->gte($v2);
		$this->assertFalse($r, "{$v1} >= {$v2}: ".Str::from($r));
		$r = $v1->gt($v2);
		$this->assertFalse($r, "{$v1} > {$v2}: ".Str::from($r));
		$r = $v1->lt($v2);
		$this->assertTrue($r, "{$v1} < {$v2}: ".Str::from($r));
		$r = $v1->lte($v2);
		$this->assertTrue($r, "{$v1} <= {$v2}: ".Str::from($r));
		$r = $v1->e($v2);
		$this->assertFalse($r, "{$v1} = {$v2}: ".Str::from($r));

		// $v1 vs $v3
		$r = $v1->gte($v3);
		$this->assertFalse($r, "{$v1} >= {$v3}: ".Str::from($r));
		$r = $v1->gt($v3);
		$this->assertFalse($r, "{$v1} > {$v3}: ".Str::from($r));
		$r = $v1->lt($v3);
		$this->assertTrue($r, "{$v1} < {$v3}: ".Str::from($r));
		$r = $v1->lte($v3);
		$this->assertTrue($r, "{$v1} <= {$v3}: ".Str::from($r));
		$r = $v1->e($v3);
		$this->assertFalse($r, "{$v1} = {$v3}: ".Str::from($r));

		// $v2 vs $v3
		$r = $v2->gte($v3);
		$this->assertFalse($r, "{$v2} >= {$v3}: ".Str::from($r));
		$r = $v2->gt($v3);
		$this->assertFalse($r, "{$v2} > {$v3}: ".Str::from($r));
		$r = $v2->lt($v3);
		$this->assertTrue($r, "{$v2} < {$v3}: ".Str::from($r));
		$r = $v2->lte($v3);
		$this->assertTrue($r, "{$v2} <= {$v3}: ".Str::from($r));
		$r = $v2->e($v3);
		$this->assertFalse($r, "{$v2} = {$v3}: ".Str::from($r));

		// $v3 vs $v4
		$r = $v3->gte($v4);
		$this->assertTrue($r, "{$v3} >= {$v4}: ".Str::from($r));
		$r = $v3->gt($v4);
		$this->assertFalse($r, "{$v3} > {$v4}: ".Str::from($r));
		$r = $v3->lt($v4);
		$this->assertFalse($r, "{$v3} < {$v4}: ".Str::from($r));
		$r = $v3->lte($v4);
		$this->assertTrue($r, "{$v3} <= {$v4}: ".Str::from($r));
		$r = $v3->e($v4);
		$this->assertTrue($r, "{$v3} = {$v4}: ".Str::from($r));

		// $v2 vs $v1
		$r = $v2->gte($v1);
		$this->assertTrue($r, "{$v2} >= {$v1}: ".Str::from($r));
		$r = $v2->gt($v1);
		$this->assertTrue($r, "{$v2} > {$v1}: ".Str::from($r));
		$r = $v2->lt($v1);
		$this->assertFalse($r, "{$v2} < {$v1}: ".Str::from($r));
		$r = $v2->lte($v1);
		$this->assertFalse($r, "{$v2} <= {$v1}: ".Str::from($r));
		$r = $v2->e($v1);
		$this->assertFalse($r, "{$v2} == {$v1}: ".Str::from($r));

		// $v4 != $v5
		$r = !$v4->e($v5);
		$this->assertTrue($r, "{$v4} != {$v5}: ".Str::from($r));

	}

	/**
	 * @return void
	 */
	public function testIncorrectOrEmptyParsingString(): void {
		$this->expectException(IncorrectVersionFormat::class);
		$obj = new Version;
	}

	/**
	 * @param $str_v1
	 *
	 * @depends testVersionObjectCreationAndParsing
	 * @dataProvider dataProviderForDebugInfo
	 *
	 * @return void
	 */
	public function testDebugInfo($str_v1): void {
		$v1 = new Version($str_v1, static::APP_NAME);
		$arr = $v1->__debugInfo();
		$this->assertArrayHasKey('software_name', $arr, 'Does debug array have software name in it');
		$this->assertArrayHasKey('parsed_version', $arr, 'Does debug array have parsed version in it');
		$this->assertArrayNotHasKey('orig_version', $arr, 'Does not include orig version in the output by default');
		$this->assertEquals(static::APP_NAME, $arr['software_name'], 'Correct software name value');
		$this->assertEquals(strval($v1), $arr['parsed_version'], 'Correct version string value');

		// Activating output of the orig version in the debug array
		Version::$debug_include_orig = true;
		$arr = $v1->__debugInfo();

		$this->assertArrayHasKey('orig_version', $arr, 'Orig version is enabled in the debug array');
		$this->assertNotEmpty($arr['orig_version'], 'Orig version is not empty in the debug array');

		// Deactivating again (due to further tests could be affected otherwise)
		Version::$debug_include_orig = false;
	}

	/**
	 * @param $str_v1
	 * @dataProvider dataProviderCommon
	 * @return void
	 */
	public function testCustomParserUsage($str_v1) {
		$v1 = new Version($str_v1, static::APP_NAME, new CustomParserSample());
		$this->assertInstanceOf(CustomParserSample::class, $v1->parser, 'Checking correct custom parser for an object');
		$this->assertEquals(100500, $v1->major, 'Checking faked major value by custom parser');
		$this->assertEquals(0, $v1->minor, 'Checking faked minor value by custom parser');
		$this->assertEquals(100501, $v1->patch, 'Checking faked patch value by custom parser');
		$this->assertEquals('TEST', $v1->build_type, 'Checking faked build type value by custom parser');
		$this->assertEquals(100502, $v1->build_revision, 'Checking faked build revision value by custom parser');
	}

	public function testBasicVersionParserNormalization() {
		$res = BasicVersionParser::normalize(null);
		$this->assertEmpty($res, 'Normalization of null must return null');

		$res = BasicVersionParser::normalize('1.2.3');
		$this->assertInstanceOf(Version::class, $res, 'String normalization creates object on the fly');
	}
}
