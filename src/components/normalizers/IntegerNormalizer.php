<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\generic\BasicValidator;

class IntegerNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	public static function process(mixed $value): int {
		return (int) $value;
	}
}
