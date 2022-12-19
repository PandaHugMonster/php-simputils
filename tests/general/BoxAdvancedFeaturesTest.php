<?php

namespace general;

use PHPUnit\Framework\TestCase;
use function spaf\simputils\basic\bx;

/**
 * @covers \spaf\simputils\models\Box
 */
class BoxAdvancedFeaturesTest extends TestCase {

	private function dataProviderForStretching() {
		return [
			[
				'Key1=Val1, Key2=Val2, Key3=Val3, Key4=Val4',
				bx([
					'Key1' => 'Val1',
					'Key2' => 'Val2',
					'Key3' => 'Val3',
					'Key4' => 'Val4',
				])->stretched(true, ', '),
			],
			[
				'Key1===Val1 # Key2===Val2 # Key3===Val3 # Key4===Val4',
				bx([
					'Key1' => 'Val1',
					'Key2' => 'Val2',
					'Key3' => 'Val3',
					'Key4' => 'Val4',
				])->stretched('===', ' # '),
			],
		];
	}

	/**
	 * @param $expected
	 * @param $box
	 *
	 * @dataProvider dataProviderForStretching
	 * @return void
	 */
	function testStretching($expected, $box) {
		$this->assertEquals($expected, "{$box}");
	}
}
