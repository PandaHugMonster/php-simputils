<?php

namespace spaf\simputils\components\validators;

use spaf\simputils\Boolean;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\ValidatorInterface;

class BooleanValidator extends SimpleObject implements ValidatorInterface {

	/**
	 * @inheritDoc
	 */
	public static function processSet(mixed $value): mixed {
		return Boolean::from($value);
	}
}
