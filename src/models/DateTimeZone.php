<?php

namespace spaf\simputils\models;

use spaf\simputils\generic\fixups\FixUpDateTimeZone;

class DateTimeZone extends FixUpDateTimeZone {

	public function __toString(): string {
		return $this->getName();
	}
}
