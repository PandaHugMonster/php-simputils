<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\generic\BasicValidator;

class FloatNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	public static function process(mixed $value): float {
		return (float) $value;
	}
}
