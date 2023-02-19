<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\DT;
use spaf\simputils\PHP;
use function define;
use function spaf\simputils\basic\pr;

/**
 * @runTestsInSeparateProcesses
 */
class BugsCoverTest extends TestCase {

	/**
	 * @return void
	 * @runInSeparateProcess
	 */
	function testBug132CurrentUrlNewInstance() {
		PHP::init();

		$_SERVER['SERVER_NAME'] = 'localhost:90/booo/fooo?godzila=tamdam#jjj';
		$_SERVER['SERVER_PORT'] = 8080;
		define('CURRENT_URL_PRETEND_NOT_CLI', true);

		$url1 = PHP::currentUrl();
		$url2 = PHP::currentUrl();

		$this->assertNotEquals($url1->obj_id, $url2->obj_id);
	}
}
