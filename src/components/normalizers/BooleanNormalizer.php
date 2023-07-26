<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\Boolean;
use spaf\simputils\generic\BasicValidator;

class BooleanNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	static function process(mixed $value): mixed {
		return Boolean::from($value);
	}
}
