<?php

namespace general\bugs;

use PHPUnit\Framework\TestCase;
use function spaf\simputils\basic\url;

class BugUrlEmptyQuestionMarkTest extends TestCase {

	/**
	 * @covers \spaf\simputils\models\UrlObject
	 *
	 * @return void
	 */
	function testOne() {
		$url = url('localhost', '//test/test?');

		$this->assertEmpty((array) $url->params);
		$this->assertEquals('/test/test', $url->relative);
	}

}
