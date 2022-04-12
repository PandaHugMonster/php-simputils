<?php /** @noinspection ALL */


use PHPUnit\Framework\TestCase;
use spaf\simputils\Boolean;
use spaf\simputils\models\BigNumber;
use function spaf\simputils\basic\env;

/**
 * @covers \spaf\simputils\models\BigNumber
 *
 * @uses \spaf\simputils\Boolean::from
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\System
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\attributes\PropertyBatch
 * @uses \spaf\simputils\attributes\PropertyBatch
 * @uses \spaf\simputils\basic\env
 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\models\PhpInfo
 * @uses \spaf\simputils\models\Version
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsPrepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsPropertyBatchMethodGet
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__set
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsGetValidator
 */
class BigNumbersTest extends TestCase {

	protected $fake_disabled_bcmath = null;
	protected $fake_disabled_gmp = null;

	public function setUp(): void {
		$this->fake_disabled_bcmath =
			Boolean::from(env('TESTS_FAKE_DISABLED_BCMATH', false));
		$this->fake_disabled_gmp =
			Boolean::from(env('TESTS_FAKE_DISABLED_GMP', false));
	}

	public function getGeneralConversion() {
		return [
			['100000000000000000000', '100000000000000000000'],
			['100000000000000000100', '100000000000000000100'],
			['12', '12'],
			['24', '24'],
			['24100000000000000000100', '24100000000000000000100'],
		];
	}

	/**
	 * Test BCMATH conversion
	 *
	 * @param string $expected
	 * @param string $val
	 *
	 * @return void
	 */
	public function testBcmathConversion() {
		$this->_extensionWiseTests(
			BigNumber::SUBSYSTEM_BCMATH,
			$this->getGeneralConversion(),
			$this->fake_disabled_bcmath,
		);
	}

	/**
	 * Test GMP conversion
	 *
	 * @param string $expected
	 * @param string $val
	 *
	 * @return void
	 */
	public function testGmpConversion() {
		$this->_extensionWiseTests(
			BigNumber::SUBSYSTEM_GMP,
			$this->getGeneralConversion(),
			$this->fake_disabled_gmp,
		);
	}

	private function _extensionWiseTests($ext, $data, $disabled) {
		$this->skipIfExtIsNotAvailable($disabled);

		foreach ($data as [$expected, $val]) {
			$bn = new BigNumber($val, extension: $ext);
			$this->assertEquals($expected, "{$bn}");
		}
	}

	private function skipIfExtIsNotAvailable($ext, $disabled = false) {
		if ($disabled || !BigNumber::checkExtensionAvailability($ext)) {
			if ($disabled) {
				$txt_faked = ' (faked through the env variable)';
			}
			$this->markTestSkipped("The extension of {$ext} is not available{$txt_faked}");
			return false;
		}

		return true;
	}

	/**
	 * @return void
	 */
	function testWithGmp() {
		$ext = BigNumber::SUBSYSTEM_GMP;
		$this->skipIfExtIsNotAvailable($ext, $this->fake_disabled_gmp);

		// Mutable
		$bn = new BigNumber(123, true, $ext);

		// NOTE Irrelevant for GMP
		$this->assertEquals("{$bn}", "{$bn->floor()}");

		$this->assertFalse($bn->fractions_supported);
		$this->assertTrue($bn->mutable);
		$this->assertEquals($ext, $bn->extension);
		$this->assertEquals('123', "$bn");

		$bn->add(22);
		$this->assertEquals('145', "$bn");

		$bn->mul('1000000000000000000999');
		$this->assertEquals('145000000000000000144855', "$bn");

		$bn->sub('500000000')->div('100000')->add(33);
		$this->assertEquals('1449999999999995034', "$bn");

		$bn->sub('999000000000');
		$this->assertEquals('1449999000999995034', "$bn");

		$bn->mod(55);
		$this->assertEquals(39, "$bn");

		$this->assertEquals(0, "{$bn->cmp(39)}");
		$this->assertEquals(-1, "{$bn->cmp(50)}");
		$this->assertEquals(1, "{$bn->cmp(30)}");

		$bn->pow(2);
		$this->assertEquals(1521, "{$bn}");

		$bn->powMod(2, 33);
		$this->assertEquals(9, "{$bn}");

		$bn->sqrt();
		$this->assertEquals(3, "{$bn}");

		$this->assertFalse($bn->isZero());

		$bn->mul(0);
		$this->assertEquals('0', "$bn");
		$this->assertTrue($bn->isZero());

		// Immutable
		$orig = $bn = new BigNumber(123, extension: $ext);

		$bn->mutable = false;

		$bn = $bn->floor();
		$this->assertEquals("{$orig}", "{$bn}");

		$this->assertFalse($bn->mutable);

		$this->assertEquals($ext, $bn->extension);

		$this->assertEquals('123', "$bn");

		$bn = $bn->add(22);

		$this->assertNotEquals($orig->obj_id, $bn->obj_id);

		$this->assertEquals('145', "$bn");

		$bn = $bn->mul('1000000000000000000999');
		$this->assertEquals('145000000000000000144855', "$bn");

		$bn = $bn->sub('500000000')->div('100000')->add(33);
		$this->assertEquals('1449999999999995034', "$bn");

		$bn = $bn->sub('999000000000');
		$this->assertEquals('1449999000999995034', "$bn");

		$bn = $bn->mod(55);
		$this->assertEquals(39, "$bn");

		$this->assertEquals(0, "{$bn->cmp(39)}");
		$this->assertEquals(-1, "{$bn->cmp(50)}");
		$this->assertEquals(1, "{$bn->cmp(30)}");

		$bn = $bn->pow(2);
		$this->assertEquals(1521, "{$bn}");

		$bn = $bn->powMod(2, 33);
		$this->assertEquals(9, "{$bn}");

		$bn = $bn->sqrt();
		$this->assertEquals(3, "{$bn}");

		$this->assertFalse($bn->isZero());

		$bn = $bn->mul(0);
		$this->assertEquals('0', "$bn");
		$this->assertTrue($bn->isZero());

	}

	/**
	 * @return void
	 */
	function testWithBcmath() {
		$ext = BigNumber::SUBSYSTEM_BCMATH;
		$this->skipIfExtIsNotAvailable($ext, $this->fake_disabled_bcmath);

		// Mutable
		$bn = new BigNumber(123.6, true, $ext);

		$this->assertEquals(123, "{$bn->floor()}");

		$this->assertTrue($bn->fractions_supported);
		$this->assertTrue($bn->mutable);
		$this->assertEquals($ext, $bn->extension);
		$this->assertEquals('123', "$bn");

		$bn->add(22);
		$this->assertEquals('145', "$bn");

		$bn->mul('1000000000000000000999');
		$this->assertEquals('145000000000000000144855', "$bn");

		$bn->sub('500000000')->div('100000')->add(33);
		$this->assertEquals('1449999999999995034', "$bn");

		$bn->sub('999000000000');
		$this->assertEquals('1449999000999995034', "$bn");

		$bn->mod(55);
		$this->assertEquals(39, "$bn");

		$this->assertEquals(0, "{$bn->cmp(39)}");
		$this->assertEquals(-1, "{$bn->cmp(50)}");
		$this->assertEquals(1, "{$bn->cmp(30)}");

		$bn->pow(2);
		$this->assertEquals(1521, "{$bn}");

		$bn->powMod(2, 33);
		$this->assertEquals(9, "{$bn}");

		$bn->sqrt();
		$this->assertEquals(3, "{$bn}");

		$this->assertFalse($bn->isZero());

		$bn->mul(0);
		$this->assertEquals('0', "$bn");
		$this->assertTrue($bn->isZero());

		// Immutable
		$orig = $bn = new BigNumber(123.58, extension: $ext);

		$bn->mutable = false;

		$bn = $bn->floor();
		$this->assertEquals(123, "{$bn}");

		$this->assertFalse($bn->mutable);

		$this->assertEquals($ext, $bn->extension);

		$this->assertEquals('123', "$bn");

		$bn = $bn->add(22);

		$this->assertNotEquals($orig->obj_id, $bn->obj_id);

		$this->assertEquals('145', "$bn");

		$bn = $bn->mul('1000000000000000000999');
		$this->assertEquals('145000000000000000144855', "$bn");

		$bn = $bn->sub('500000000')->div('100000')->add(33);
		$this->assertEquals('1449999999999995034', "$bn");

		$bn = $bn->sub('999000000000');
		$this->assertEquals('1449999000999995034', "$bn");

		$bn = $bn->mod(55);
		$this->assertEquals(39, "$bn");

		$this->assertEquals(0, "{$bn->cmp(39)}");
		$this->assertEquals(-1, "{$bn->cmp(50)}");
		$this->assertEquals(1, "{$bn->cmp(30)}");

		$bn = $bn->pow(2);
		$this->assertEquals(1521, "{$bn}");

		$bn = $bn->powMod(2, 33);
		$this->assertEquals(9, "{$bn}");

		$bn = $bn->sqrt();
		$this->assertEquals(3, "{$bn}");

		$this->assertFalse($bn->isZero());

		$bn = $bn->mul(0);
		$this->assertEquals('0', "$bn");
		$this->assertTrue($bn->isZero());

	}

}
