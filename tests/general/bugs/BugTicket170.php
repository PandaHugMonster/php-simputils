<?php

namespace general\bugs;

use PHPUnit\Framework\TestCase;
use function spaf\simputils\basic\ts;

/**
 * @covers DatePeriod
 */
class BugTicket170 extends TestCase {

	function testMain() {
		$point = "2023-11-17 17:00:00";
		$ts = ts($point);
		$period = $ts->period("-24 hours");

		$this->assertEquals("2023-11-16 17:00:00", $period->start);
		$this->assertEquals($point, $period->end);

		$this->assertEquals("2023-11-16 17:00:00 - {$ts}", "{$period}");
	}

}
