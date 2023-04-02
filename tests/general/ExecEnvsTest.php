<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\exceptions\ExecEnvException;
use spaf\simputils\PHP;
use function spaf\simputils\basic\ic;

/**
 * @runTestsInSeparateProcesses
 */
class ExecEnvsTest extends TestCase {

	public function dataProviderDefaultCases() {
		return [
			// NOTE dev-local
			['dev-local', 'prod', false, false],
			['dev-local', 'demo', false, false],
			['dev-local', 'dev', false, true],

			['dev-local', 'prod-local', false, false],
			['dev-local', 'demo-local', false, false],
			['dev-local', 'dev-local', false, true],

			// NOTE dev
			['dev', 'prod', false, false],
			['dev', 'demo', false, false],
			['dev', 'dev', false, true],

			['dev', 'prod-local', false, false],
			['dev', 'demo-local', false, false],
			['dev', 'dev-local', false, false],

			// NOTE demo-local
			['demo-local', 'prod', false, false],
			['demo-local', 'demo', false, true],
			['demo-local', 'dev', false, false],

			['demo-local', 'prod-local', false, false],
			['demo-local', 'demo-local', false, true],
			['demo-local', 'dev-local', false, false],

			// NOTE demo
			['demo', 'prod', false, false],
			['demo', 'demo', false, true],
			['demo', 'dev', false, false],

			['demo', 'prod-local', false, false],
			['demo', 'demo-local', false, false],
			['demo', 'dev-local', false, false],

			// NOTE prod-local
			['prod-local', 'prod', false, true],
			['prod-local', 'demo', false, false],
			['prod-local', 'dev', false, false],

			['prod-local', 'prod-local', false, true],
			['prod-local', 'demo-local', false, false],
			['prod-local', 'dev-local', false, false],

			// NOTE prod
			['prod', 'prod', false, true],
			['prod', 'demo', false, false],
			['prod', 'dev', false, false],

			['prod', 'prod-local', false, false],
			['prod', 'demo-local', false, false],
			['prod', 'dev-local', false, false],

			////

			// NOTE hierarchical dev-local
			['dev-local', 'prod', true, true],
			['dev-local', 'demo', true, true],
			['dev-local', 'dev', true, true],

			['dev-local', 'prod-local', true, true],
			['dev-local', 'demo-local', true, true],
			['dev-local', 'dev-local', true, true],

			// NOTE hierarchical dev
			['dev', 'prod', true, true],
			['dev', 'demo', true, true],
			['dev', 'dev', true, true],

			['dev', 'prod-local', true, false],
			['dev', 'demo-local', true, false],
			['dev', 'dev-local', true, false],

			// NOTE hierarchical demo-local
			['demo-local', 'prod', true, true],
			['demo-local', 'demo', true, true],
			['demo-local', 'dev', true, false],

			['demo-local', 'prod-local', true, true],
			['demo-local', 'demo-local', true, true],
			['demo-local', 'dev-local', true, false],

			// NOTE hierarchical demo
			['demo', 'prod', true, true],
			['demo', 'demo', true, true],
			['demo', 'dev', true, false],

			['demo', 'prod-local', true, false],
			['demo', 'demo-local', true, false],
			['demo', 'dev-local', true, false],

			// NOTE hierarchical prod-local
			['prod-local', 'prod', true, true],
			['prod-local', 'demo', true, false],
			['prod-local', 'dev', true, false],

			['prod-local', 'prod-local', true, true],
			['prod-local', 'demo-local', true, false],
			['prod-local', 'dev-local', true, false],

			// NOTE hierarchical prod
			['prod', 'prod', true, true],
			['prod', 'demo', true, false],
			['prod', 'dev', true, false],

			['prod', 'prod-local', true, false],
			['prod', 'demo-local', true, false],
			['prod', 'dev-local', true, false],
		];
	}

	/**
	 * @covers       \spaf\simputils\generic\BasicExecEnvHandler
	 *
	 * @dataProvider dataProviderDefaultCases
	 *
	 * @param $ee
	 * @param $check_ee
	 * @param $is_hierarchical
	 * @param $expected_bool
	 *
	 * @return void
	 * @throws ExecEnvException
	 */
	function testDefaultCases($ee, $check_ee, $is_hierarchical, $expected_bool) {
		$ic = ic();
		$ic->ee = $ee;
		$ic->ee->is_hierarchical = $is_hierarchical;

		$this->assertEquals($expected_bool, $ic->ee->is($check_ee), "Of values: $ee, $check_ee, $is_hierarchical, $expected_bool");
	}

	public function dataProviderStrictAndArrayCases() {
		return [
			['dev-local', 'prod', false],
			['dev-local', 'prod-local', false],
			['dev-local', 'demo', false],
			['dev-local', 'demo-local', false],
			['dev-local', 'dev', false],
			['dev-local', 'dev-local', true],

			['dev', 'prod', false],
			['dev', 'prod-local', false],
			['dev', 'demo', false],
			['dev', 'demo-local', false],
			['dev', 'dev', true],
			['dev', 'dev-local', false],

			['prod', 'prod', true],
			['prod', 'prod-local', false],
			['prod', 'demo', false],
			['prod', 'demo-local', false],
			['prod', 'dev', false],
			['prod', 'dev-local', false],

			['prod', ['dev-local', 'prod', 'demo'], true],
			['prod-local', ['dev-local', 'prod', 'demo'], false],
			['demo', ['dev-local', 'prod', 'demo'], true],
			['dev', ['dev-local', 'prod', 'demo'], false],
			['dev-local', ['dev-local', 'prod', 'demo'], true],
		];
	}

	/**
	 *
	 * @dataProvider dataProviderStrictAndArrayCases
	 *
	 * @param $ee
	 * @param $check_ee
	 * @param $expected_bool
	 *
	 * @return void
	 * @throws ExecEnvException
	 * @covers       \spaf\simputils\generic\BasicExecEnvHandler
	 *
	 */
	function testStrictAndArrayCases($ee, $check_ee, $expected_bool) {
		$ic = ic();
		$ic->ee = $ee;
		$ic->ee->is_hierarchical = false;

		if (PHP::isArrayCompatible($check_ee)) {
			$check_ee = PHP::box($check_ee);
		}

		$this->assertEquals(
			$expected_bool,
			$ic->ee->is($check_ee, is_strict: true),
			"Of values: $ee, $check_ee, $expected_bool",
		);
	}

	protected function setUp(): void {
		$ic = PHP::init();
	}

}
