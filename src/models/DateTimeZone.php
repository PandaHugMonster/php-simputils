<?php

namespace spaf\simputils\models;

class DateTimeZone extends \DateTimeZone {

	public function __toString(): string {
		return $this->getName();
	}
}
