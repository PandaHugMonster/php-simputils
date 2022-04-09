<?php

namespace spaf\simputils\components\validators;

use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\ValidatorInterface;

class FloatValidator extends SimpleObject implements ValidatorInterface {

	/**
	 * @inheritDoc
	 */
	public static function processSet(mixed $value): float {
		return (float) $value;
	}
}
