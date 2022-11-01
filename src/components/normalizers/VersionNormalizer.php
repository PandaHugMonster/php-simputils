<?php

namespace spaf\simputils\components\normalizers;

use spaf\simputils\generic\BasicValidator;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;

class VersionNormalizer extends BasicValidator {

	/**
	 * @inheritDoc
	 */
	public static function process(mixed $value): mixed {
		$class = PHP::redef(Version::class);
		return new $class($value);
	}
}
