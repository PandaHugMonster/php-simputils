<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\exceptions\CallableAbsent;
use spaf\simputils\models\Password;
use spaf\simputils\models\Secret;

/**
 * @covers \spaf\simputils\models\Secret
 * @covers \spaf\simputils\models\Password
 *
 * @uses   \spaf\simputils\Str
 * @uses   \spaf\simputils\attributes\Property
 * @uses   \spaf\simputils\traits\PropertiesTrait
 */
class PasswordsAndSecretsTest extends TestCase {

	/**
	 * @return void
	 */
	function testSecretsGeneral() {
		$secret = new Secret;

		$this->assertEmpty($secret->value);
		$this->assertEquals('', "{$secret}");

		$secret = new Secret($s = 'q1w-E5t6T7l-r8t1Y__23');
		$this->assertEquals($s, $secret->value);
		$this->assertEquals('**[secret]**', "{$secret}");

		$secret = new Secret($s = 'BE-fe-GG-wp!-!-!', $n = 'spec-token');
		$this->assertEquals($s, $secret->value);
		$this->assertEquals('**[S:spec-token]**', "{$secret}");
		$this->assertEquals($n, "{$secret->name}");

		$this->assertEquals($s, "{$secret->for_system}");
		$this->assertEquals('**[S:spec-token]**', "{$secret->for_user}");
	}

	/**
	 * @return void
	 */
	function testPasswordsGeneral() {
		$pass = new Password;

		$this->assertEmpty($pass->value);
		$this->assertEquals('', "{$pass}");

		$pass = new Password($p = 'beu4bra@rqw_VEQ9wpd');
		$this->assertEquals($p, $pass->value);
		$this->assertEquals('**[password]**', "{$pass}");

		$pass = new Password($p = 'mca3FCA@njd0pru0hkc', $n = 'spec-password');
		$this->assertEquals($p, $pass->value);
		$this->assertEquals('**[P:spec-password]**', "{$pass}");
		$this->assertEquals($n, "{$pass->name}");

		$this->assertEquals($p, "{$pass->for_system}");
		$this->assertEquals('**[P:spec-password]**', "{$pass->for_user}");
	}

	/**
	 * @return void
	 * @throws CallableAbsent
	 */
	function testPasswordsExtended() {
		$pass1 = new Password($p1 = 'fam@grf8kcv4VXN1ujt');
		$p2 = 'testTESTtestTesttes';

		$pass3 = new Password($p1);

		$this->assertFalse($pass1->verifyPassword($p2,));
		$this->assertTrue($pass3->verifyPassword($p1));
	}

}
