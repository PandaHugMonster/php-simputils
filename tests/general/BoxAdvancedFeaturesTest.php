<?php

namespace general;

use PHPUnit\Framework\TestCase;
use function spaf\simputils\basic\bx;

/**
 * @covers \spaf\simputils\models\Box
 */
class BoxAdvancedFeaturesTest extends TestCase {

	function dataProviderForStretching() {
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
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\basic\bx
	 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
	 * @dataProvider dataProviderForStretching
	 * @return void
	 */
	function testStretching($expected, $box) {
		$this->assertEquals($expected, "{$box}");
	}
}
