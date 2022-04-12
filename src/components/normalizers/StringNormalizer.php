<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\generic\BasicValidator;
use spaf\simputils\Str;

class StringNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	public static function process(mixed $value): string {
		return Str::from($value);
	}
}
