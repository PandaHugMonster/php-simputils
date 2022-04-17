<?php

namespace spaf\simputils\models;

use spaf\simputils\generic\fixups\FixUpDatePeriod;

class DatePeriod extends FixUpDatePeriod {

	/**
	 *
	 * TODO Add "interval" part to the string
	 * @return string
	 */
	public function __toString(): string {
		return "{$this->start} - {$this->end}";
	}
}
