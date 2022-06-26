<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\generic\BasicValidator;
use spaf\simputils\PHP;

class UrlNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	public static function process(mixed $value): mixed {
		return PHP::url($value);
	}
}
