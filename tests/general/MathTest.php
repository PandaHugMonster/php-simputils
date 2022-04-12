<?php /** @noinspection ALL */

namespace general;

use Generator;
use PHPUnit\Framework\TestCase;
use spaf\simputils\Math;
use spaf\simputils\PHP;
use function abs;
use function bindec;
use function decbin;
use function decoct;
use function deg2rad;
use function getrandmax;
use function pi;
use function round;

/**
 * Absolutely redundant and unnecessary most likely!
 * @covers \spaf\simputils\Math
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 */
class MathTest extends TestCase {

	function testAllShortcuts() {
		$this->assertEquals(abs($val = -1), Math::abs($val));
		$this->assertEquals(acos($val = -1), Math::acos($val));
		$this->assertEquals(acosh($val = 1), Math::acosh($val));
		$this->assertEquals(asin($val = -1), Math::asin($val));
		$this->assertEquals(asinh($val = -1), Math::asinh($val));
		$this->assertEquals(atan2($val = -1, $val2 = 1), Math::atan2($val, $val2));
		$this->assertEquals(atan($val = -1), Math::atan($val));
		$this->assertEquals(atanh($val = -1), Math::atanh($val));
		$this->assertEquals(
			base_convert($val = 'a37334', $val2 = 16, $val3 = 2),
			Math::baseConvert($val, $val2, $val3)
		);


		$this->assertEquals(bindec($val = '11011001'), Math::bin2dec($val));
		$this->assertEquals(ceil($val = 34.3), Math::ceil($val));
		$this->assertEquals(cos($val = 1), Math::cos($val));
		$this->assertEquals(cosh($val = 1), Math::cosh($val));
		$this->assertEquals(decbin($val = 102), Math::dec2bin($val));
		$this->assertEquals(dechex($val = 102), Math::dec2hex($val));
		$this->assertEquals(decoct($val = 102), Math::dec2oct($val));
		$this->assertEquals(deg2rad($val = 102), Math::deg2rad($val));
		$this->assertEquals(exp($val = 5), Math::exp($val));
		$this->assertEquals(expm1($val = 5), Math::expm1($val));
		$this->assertEquals(fdiv($val = 5, $val2 = 2), Math::fdiv($val, $val2));
		$this->assertEquals(floor($val = 5.59), Math::floor($val));
		$this->assertEquals(fmod($val = 5, $val2 = 2), Math::fmod($val, $val2));
		$this->assertEquals(getrandmax(), Math::getRandMax());
		$this->assertEquals(hexdec($val = 'FFFFFF'), Math::hex2dec($val));
		$this->assertEquals(hypot($val = 2, $val2 = 3), Math::hypot($val, $val2));
		$this->assertEquals(intdiv($val = 2, $val2 = 3), Math::intdiv($val, $val2));
		$this->assertEquals(is_finite($val = 3), Math::isFinite($val));
		$this->assertEquals(is_infinite($val = 3), Math::isInfinite($val));
		$this->assertEquals(is_nan($val = 3), Math::isNan($val));

		$this->assertEquals(log10($val = 3), Math::log10($val));
		$this->assertEquals(log1p($val = 3), Math::log1p($val));
		$this->assertEquals(log($val = 3), Math::log($val));
		$this->assertEquals(max($val = [1, 4, 2, 0, 99, 1, -20]), Math::max($val));
		$this->assertEquals(min($val = [1, 4, 2, 0, 99, 1, -20]), Math::min($val));
		$this->assertEquals(octdec($val = '565656'), Math::oct2dec($val));
		$this->assertEquals(pi(), Math::pi());
		$this->assertEquals(pow($val = 3, $val2 = 3), Math::pow($val, $val2));
		$this->assertEquals(rad2deg($val = 3), Math::rad2deg($val));
		$this->assertEquals(round($val = 3.5), Math::round($val));
		$this->assertEquals(sin($val = -1), Math::sin($val));
		$this->assertEquals(sinh($val = -1), Math::sinh($val));
		$this->assertEquals(sqrt($val = 23), Math::sqrt($val));
		$this->assertEquals(tan($val = 1), Math::tan($val));
		$this->assertEquals(tanh($val = 1), Math::tanh($val));
	}

	function testDivMod() {
		[$a, $b] = Math::divmod(19, 5);
		$this->assertEquals(3, $a, 'quotient check');
		$this->assertEquals(4, $b, 'remainder check');
	}

	function testRangeObject() {
		$gen = Math::range(1, 4);
		$this->assertInstanceOf(Generator::class, $gen);
	}

	public function rangeGroupsDataProvider() {
		return [
			[   // Normal positive increasing range
				[1, 2, 3, 4],
				Math::range(1, 4)
			],
			[   // Normal positive decreasing range
				[10, 9, 8, 7, 6, 5],
				Math::range(10, 5)
			],
			[   // Normal cross increasing range
				[-3, -2, -1, 0, 1, 2, 3],
				Math::range(-3, 3)
			],
			[   // Normal cross decreasing range
				[3, 2, 1, 0, -1, -2, -3],
				Math::range(3, -3)
			],
			[   // Normal negative increasing range
				[-10, -9, -8, -7, -6],
				Math::range(-10, -6)
			],
			[   // Normal negative decreasing range
				[-5, -6, -7],
				Math::range(-5, -7)
			],
		];
	}

	/**
	 * @dataProvider rangeGroupsDataProvider
	 *
	 * @return void
	 */
	function testRange($expected, $range) {
		$res = PHP::box($range);
		$expected = PHP::box($expected);

		$this->assertEquals($expected->size, $res->size);
		$this->assertEquals($expected, $res);
	}
}
