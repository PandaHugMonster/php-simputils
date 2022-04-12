<?php

namespace spaf\simputils\models\files\apps;

use spaf\simputils\generic\BasicResource;
use spaf\simputils\PHP;

/**
 * Json data processor
 */
class JsonProcessor extends TextProcessor {

	public function getContent(mixed $fd, ?BasicResource $file = null): mixed {
		$res = PHP::deserialize(
			parent::getContent($fd, $file),
			enforced_type: PHP::SERIALIZATION_TYPE_JSON
		);
		return $res;
	}

	public function setContent(mixed $fd, $data, ?BasicResource $file = null): void {
		$res = PHP::serialize($data, enforced_type: PHP::SERIALIZATION_TYPE_JSON);
		parent::setContent($fd, $res, $file);
	}
}
