<?php /** @noinspection ALL */


use PHPUnit\Framework\TestCase;
use spaf\simputils\Boolean;
use spaf\simputils\models\BigNumber;
use spaf\simputils\models\DataUnit;
use function spaf\simputils\basic\du;
use function spaf\simputils\basic\env;

/**
 * @covers \spaf\simputils\Data
 * @covers \spaf\simputils\basic\du
 * @covers \spaf\simputils\models\DataUnit
 *
 * @uses \spaf\simputils\models\BigNumber
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
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____prepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____propertyBatchMethodGet
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__set
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 */
class DataUnitsTest extends TestCase {

	protected $fake_disabled_bcmath = null;
	protected $fake_disabled_gmp = null;

	public function setUp(): void {
		$this->fake_disabled_bcmath =
			Boolean::from(env('TESTS_FAKE_DISABLED_BCMATH', false));
		$this->fake_disabled_gmp =
			Boolean::from(env('TESTS_FAKE_DISABLED_GMP', false));
	}

	public function getNormalTestData() {
		return [
			['0 B', null],
			['0 B', ''],
			['30 KB', new DataUnit('30KB')],
			['26 B', 26],

			['1 KB', '1024b'],
			['1 MB', '1024kb'],
			['1 GB', '1024mb'],
			['1 TB', '1024gb'],
			['1 PB', '1024tb'],
			['1 EB', '1024Pb'],
			['1 ZB', '1024eb'],
			['1 YB', '1024zb'],
			['1000 YB', '1024000zb'],
		];
	}

	public function getRandomTestData($fract) {
		return [
			[$fract?'2.92 MB':'2 MB', '3000kb'],
			[$fract?'9.76 TB':'9 TB', '9999GB'],
			[$fract?'1.47 KB':'1 KB', '1515b'],
			[$fract
				?'9864267676767676.76 YB':'9864267676767676 YB',
				'10101010101010101010ZB'
			],
			[$fract
				?'8470329472543003390683225006796419620513916015624.99 YB'
				:'8470329472543003390683225006796419620513916015624 YB',
				'9999999999999999999999999999999999999999999999999999999999999999999999KB'
			],
			[$fract
				?'1 B'
				:'1 B',
				'1b'
			],

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
			$this->getNormalTestData(),
			$this->fake_disabled_bcmath,
		);
		$this->_extensionWiseTests(
			BigNumber::SUBSYSTEM_BCMATH,
			$this->getRandomTestData(true),
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
			$this->getNormalTestData(),
			$this->fake_disabled_gmp,
		);
		$this->_extensionWiseTests(
			BigNumber::SUBSYSTEM_GMP,
			$this->getRandomTestData(false),
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
		DataUnit::$big_number_extension = $ext;

		foreach ($data as [$expected, $val]) {
			$du = du($val);
			$this->assertEquals($expected, $du->for_user);
		}
	}

}
