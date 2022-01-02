<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\generic\BasicSystemFingerprint;
use spaf\simputils\models\SystemFingerprint;
use spaf\simputils\System;

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
		$this->assertIsString($val = System::os());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = System::systemName());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = System::kernelName());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = System::kernelRelease());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = System::kernelVersion());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = System::cpuArchitecture());
		$this->assertNotEmpty($val);

		$this->assertIsString($val = System::serverApi());
		$this->assertNotEmpty($val);

		$this->assertIsObject($val = System::systemFingerprint());
		$this->assertInstanceOf(BasicSystemFingerprint::class, $val);
		$this->assertIsString(strval($val));
	}

	/**
	 * @covers \spaf\simputils\models\SystemFingerprint
	 * @covers \spaf\simputils\generic\BasicSystemFingerprint
	 * @uses \spaf\simputils\System
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
		$d = System::systemFingerprint();

		$fp = new SystemFingerprint();
		$this->assertInstanceOf(SystemFingerprint::class, $fp);

		$fp = SystemFingerprint::parse(strval($d));
		$this->assertInstanceOf(SystemFingerprint::class, $fp);
	}
}
