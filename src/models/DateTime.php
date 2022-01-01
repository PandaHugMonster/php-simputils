<?php


namespace spaf\simputils\models;

use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\traits\PropertiesTrait;

/**
 * @todo    Add more reasonable fields like year and month, etc.
 */
class DateTime extends \DateTime {
	use PropertiesTrait;

	public function __toString(): string {
		return DateTimeHelper::stringify($this);
	}
}
