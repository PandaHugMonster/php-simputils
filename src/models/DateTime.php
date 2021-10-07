<?php


namespace spaf\simputils\models;

use spaf\simputils\helpers\DateTimeHelper;

/**
 * @todo    Add more reasonable fields like year and month, etc.
 */
class DateTime extends \DateTime {

	public function __toString(): string {
		return DateTimeHelper::stringify($this);
	}

}