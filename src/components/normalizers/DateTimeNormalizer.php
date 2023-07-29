<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\DT;
use spaf\simputils\generic\BasicValidator;

class DateTimeNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	static function process(mixed $value): mixed {
		return DT::ts($value, true);
	}
}
