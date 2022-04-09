<?php

namespace spaf\simputils\components\validators;

use spaf\simputils\DT;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\ValidatorInterface;

class DateTimeValidator extends SimpleObject implements ValidatorInterface {

	/**
	 * @inheritDoc
	 */
	public static function processSet(mixed $value): mixed {
		return DT::ts($value, true);
	}
}
