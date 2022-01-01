<?php


namespace spaf\simputils\models;

use spaf\simputils\DT;
use spaf\simputils\traits\PropertiesTrait;

/**
 * @todo    Add more reasonable fields like year and month, etc.
 */
class DateTime extends \DateTime {
	use PropertiesTrait;

	public function __toString(): string {
		return DT::stringify($this);
	}
}
