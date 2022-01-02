<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\special\CodeBlocksCacheIndex;

/**
 * @uses \spaf\simputils\models\Version
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
 * @uses \spaf\simputils\traits\MetaMagic
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\basic\pd()
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\generic\BasicVersionParser
 */
class SettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function testInfoPart() {
		$version_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);
		$version = PHP::simpUtilsVersion();
		$this->assertInstanceOf($version_class::class, $version, 'Is version an object');

		$sum = $version->major + $version->minor + $version->patch;
		$this->assertGreaterThan(0, $sum, 'Checking if it\'s not an empty version');

		$license = PHP::simpUtilsLicense();
		$this->assertIsString($license, 'Checking if it has a license');
	}

	/**
	 * FIX  Refactor
	 * @return void
	 */
	public function testSettingsRedefinition() {
//		Settings::redefinePd(function (...$data) {
//			echo "redefined...\n";
//			foreach ($data as $d)
//				print_r($d);
//		});
//
//		$version = PHP::simpUtilsVersion();
//
//		pd($version);
//		$this->expectOutputString("redefined...\n".print_r($version, true));
//
//		$res = Settings::getRedefined('non-existing-component');
//		$this->assertNull($res, 'Should be null if not existing');
	}

//	public function testCaseRedefinition() {
//		Settings::setSimpleObjectTypeCase(Settings::SO_SNAKE_CASE);
//		$this->assertEquals(Settings::SO_SNAKE_CASE, Settings::getSimpleObjectTypeCase(), 'Checking if changed');
//
//		Settings::setSimpleObjectTypeCase(Settings::SO_CAMEL_CASE);
//		$this->assertEquals(Settings::SO_CAMEL_CASE, Settings::getSimpleObjectTypeCase(), 'Checking if changed back');
//
//		$this->expectException(ValueError::class);
//		Settings::setSimpleObjectTypeCase('FAKE-sTyLe');
//	}
}
