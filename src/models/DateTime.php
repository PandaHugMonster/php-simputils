<?php


namespace spaf\simputils\models;

use spaf\simputils\helpers\DateTimeHelper;

class DateTime extends \DateTime {

	public function __toString(): string {
		return DateTimeHelper::stringify($this);
	}

}