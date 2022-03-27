<?php /** @noinspection ALL */


use PHPUnit\Framework\TestCase;
use spaf\simputils\Boolean;
use spaf\simputils\models\BigNumber;
use function spaf\simputils\basic\env;

/**
 * @covers \spaf\simputils\Data
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
	 * @throws \Exception
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
	 * @throws \Exception
	 */
	public function testGmpConversion() {
		$this->_extensionWiseTests(
			BigNumber::SUBSYSTEM_GMP,
			$this->getGeneralConversion(),
			$this->fake_disabled_gmp,
		);
	}

	private function _extensionWiseTests($ext, $data, $disabled) {
		if ($disabled || !BigNumber::checkExtensionAvailability($ext)) {
			if ($disabled) {
				$txt_faked = ' (faked through the env variable)';
			}
			$this->markTestSkipped("The extension of {$ext} is not available{$txt_faked}");
		}
		foreach ($data as [$expected, $val]) {
			$bn = new BigNumber($val, extension: $ext);
			$this->assertEquals($expected, "{$bn}");
		}
	}

}
