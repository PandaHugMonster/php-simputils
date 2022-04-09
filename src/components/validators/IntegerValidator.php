<?php

namespace spaf\simputils\components\validators;

use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\ValidatorInterface;

class IntegerValidator extends SimpleObject implements ValidatorInterface {

	/**
	 * @inheritDoc
	 */
	public static function processSet(mixed $value): int {
		return (int) $value;
	}
}
