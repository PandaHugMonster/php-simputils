<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\generic\BasicSystemFingerprint;
use spaf\simputils\models\SystemFingerprint;
use spaf\simputils\models\Version;
use spaf\simputils\System;

/**
 *
 * @covers \spaf\simputils\System
 *
 * @uses spaf\simputils\attributes\Property
 * @uses spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\Str
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
		/** @var SystemFingerprint $d */
		$d = System::systemFingerprint();

		$fp = new SystemFingerprint();
		$this->assertInstanceOf(SystemFingerprint::class, $fp);

		$fp = SystemFingerprint::parse(strval($d));
		$this->assertInstanceOf(SystemFingerprint::class, $fp);

		$res = $d->generateString(true);
		$this->assertGreaterThan(3, $res);

		$this->assertTrue($d->fits(System::systemFingerprint(new Version('12.12.12'))));
		$this->assertFalse($d->fits(null));
	}

	/**
	 * @covers \spaf\simputils\generic\BasicSystemFingerprint
	 * @uses \spaf\simputils\models\Box
	 * @uses \spaf\simputils\traits\SimpleObjectTrait
	 *
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testCustomFingerPrintParsingException() {
		$this->expectException(ValueError::class);
		SystemFingerprint::parse(
			'PAN/cac55efcadcea418138717390e7ec654,5ed77353205283d243ec7d0f5804cfb367ec5149b51cd2362d9f6a4659da2cba/0.2.3/0'
		);
	}
}
