<?php

namespace spaf\simputils\models;

use spaf\simputils\generic\fixups\FixUpDatePeriod;

class DatePeriod extends FixUpDatePeriod {

	/**
	 *
	 * FIX  Maybe add "interval"
	 * @return string
	 */
	public function __toString(): string {
		return "{$this->start} - {$this->end}";
	}
}
