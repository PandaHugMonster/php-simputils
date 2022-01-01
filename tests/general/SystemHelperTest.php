<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\components\SystemFingerprint;
use spaf\simputils\generic\BasicSystemFingerprint;
use spaf\simputils\helpers\SystemHelper;

/**
 *
 */
class SystemHelperTest extends TestCase {

	/**
	 *
	 *
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testBasics() {
		$this->assertIsString($val = SystemHelper::os());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = SystemHelper::systemName());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = SystemHelper::kernelName());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = SystemHelper::kernelRelease());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = SystemHelper::kernelVersion());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = SystemHelper::cpuArchitecture());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = SystemHelper::serverApi());
		$this->assertNotEmpty($val);

		$this->assertIsObject($val = SystemHelper::systemFingerprint());
		$this->assertInstanceOf(BasicSystemFingerprint::class, $val);
		$this->assertIsString(strval($val));
	}

	/**
	 * @covers \spaf\simputils\components\SystemFingerprint
	 * @covers \spaf\simputils\generic\BasicSystemFingerprint
	 * @uses \spaf\simputils\helpers\SystemHelper
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\generic\BasicVersionParser
	 * @uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\traits\MetaMagic
	 * @uses \spaf\simputils\traits\PropertiesTrait
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
	 * @uses \spaf\simputils\models\Box
	 * @uses \spaf\simputils\traits\dsf\DsfVersionsMethodsTrait
	 */
	public function testDefaultFingerPrint() {
		$d = SystemHelper::systemFingerprint();

		$fp = new SystemFingerprint();
		$this->assertInstanceOf(SystemFingerprint::class, $fp);

		$fp = SystemFingerprint::parse(strval($d));
		$this->assertInstanceOf(SystemFingerprint::class, $fp);
	}
}
