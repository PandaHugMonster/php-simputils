<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\DT;
use spaf\simputils\PHP;

/**
 * @runTestsInSeparateProcesses
 */
class OthersTest extends TestCase {

	/**
	 * @covers \spaf\simputils\models\L10n
	 * @covers \spaf\simputils\traits\MetaMagic
	 * @return void
	 */
	function testL10n() {
		$config = PHP::init();

		$str = '2022-04-03 12:13:01';

		$config->l10n = 'RU';
		$dt = DT::ts($str);
		$this->assertEquals('03.04.2022 15:13', $dt->for_user);

		$config->l10n = 'US';
		$dt = DT::ts($str);
		$this->assertEquals('04/03/2022 08:13 AM', $dt->for_user);
	}
}
