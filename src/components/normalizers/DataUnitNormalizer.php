<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\Data;
use spaf\simputils\generic\BasicValidator;

class DataUnitNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	static function process(mixed $value): mixed {
		return Data::du($value);
	}
}
