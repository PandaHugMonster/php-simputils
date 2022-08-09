<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use function spaf\simputils\basic\bx;

/**
 * @runTestsInSeparateProcesses
 */
class NewFeature202207Test extends TestCase {

	protected function setUp(): void {
		PHP::init([
			'l10n' => 'AT',
		]);
	}

	/**
	 * @covers \spaf\simputils\models\Box
	 * @return void
	 */
	function testBoxBatchFunctionality() {
		$a = bx([
			'key 1' => 'BIG TEXT',
			'key 2' => 22.22,
			'key 3' => 100_500,
			'key 4' => new Version('1.2.3 DEV', 'SPEC APP'),
			'99 Luftballons' => 'hehehe',
		]);

		// NOTE Case 1
		[$k1, $k2, $k3, $k4] = $a->batch(['key 1', 'key 2', 'key 3', 'key 4'], true);

		$this->assertEquals($a['key 1'], $k1);
		$this->assertEquals($a['key 2'], $k2);
		$this->assertEquals($a['key 3'], $k3);
		$this->assertEquals($a['key 4'], $k4);

		// NOTE Case 2
		extract($a->batch(['key 1', 'key 3', '99 Luftballons']));

		$this->assertEquals('BIG TEXT', $key_1);
		$this->assertEquals(100_500, $key_3);
		$this->assertEquals('hehehe', $_99_Luftballons);
	}

}
