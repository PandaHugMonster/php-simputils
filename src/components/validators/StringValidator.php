<?php

namespace spaf\simputils\components\validators;

use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\ValidatorInterface;
use spaf\simputils\Str;

class StringValidator extends SimpleObject implements ValidatorInterface {

	/**
	 * @inheritDoc
	 */
	public static function processSet(mixed $value): string {
		return Str::from($value);
	}
}
