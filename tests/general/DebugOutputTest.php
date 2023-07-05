<?php

namespace general;

use PHPUnit\Framework\TestCase;
use function spaf\simputils\basic\pr;

class DebugOutputTest extends TestCase {

	/**
	 * @covers \spaf\simputils\basic\pr
	 * @return void
	 */
	function testPrimitives() {
		$this->expectOutputString(
			"1; 	
2.3; 	
\"test\"; 	
null; 	
true; 	
false; 	
"
		);
		pr(1, 2.3, 'test', null, true, false);
	}

}
