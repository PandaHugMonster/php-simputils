<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\FS;
use spaf\simputils\models\files\apps\DotEnvProcessor;
use spaf\simputils\PHP;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\env;
use function spaf\simputils\basic\fl;

/**
 * @covers \spaf\simputils\models\files\apps\DotEnvProcessor
 */
class DotEnvTest extends TestCase {

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testDefaultBehaviour() {
		$path = "/tmp/simputils/tests/DotEnvTest/default";
		FS::mkDir($path);
		$dotenv_file = fl("{$path}/.env", DotEnvProcessor::class);
		$prefix = "NOUK_PHOC_MAPT_KNIF";
		$data = bx([
			"{$prefix}_MY_DENV_KEY_1" => 1,
			"{$prefix}_MY_DENV_KEY_2" => "2",
			"{$prefix}_MY_DENV_KEY_3" => "Three",
		]);

		$dotenv_file->content = $data;

		PHP::init([
			"working_dir" => $path,
		]);

		$relevant = PHP::allEnvs()->extract(...$data->keys);

		foreach ($data as $k => $v) {
			$this->assertEquals(
				$v, $relevant[$k], "Checking the PHP::allEnvs() value"
			);
			$this->assertEquals($v, env($k), "Checking the env() value");
		}

	}

}
