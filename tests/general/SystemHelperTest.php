<?php


use PHPUnit\Framework\TestCase;
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

	}

}
