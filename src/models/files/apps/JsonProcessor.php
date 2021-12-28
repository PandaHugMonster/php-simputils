<?php

namespace spaf\simputils\models\files\apps;

use spaf\simputils\generic\BasicResource;
use spaf\simputils\PHP;

/**
 * Json data processor
 */
class JsonProcessor extends TextProcessor {

	public static function getContent(mixed $stream, ?BasicResource $file = null): mixed {
		return PHP::deserialize(
			parent::getContent($stream, $file),
			enforced_type: PHP::SERIALIZATION_TYPE_JSON
		);
	}

	public static function setContent(mixed $stream, $data, ?BasicResource $file = null): void {
		$res = PHP::serialize($data, enforced_type: PHP::SERIALIZATION_TYPE_JSON);
		parent::setContent($stream, $res, $file);
	}
}
