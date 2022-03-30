<?php

namespace spaf\simputils\models;

use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use spaf\simputils\traits\RedefinableComponentTrait;
use function implode;

/**
 * String object
 *
 * IMP  Unfinished concept
 * @codeCoverageIgnore
 */
class StrObj extends SimpleObject {
	use RedefinableComponentTrait;

	private string $_value = '';

	public function __construct(string ...$strings) {
		$strings = PHP::box($strings);
		$sep = $strings->get('sep', '');
		if ($strings->containsKey('sep')) {
			$strings = $strings->unsetByKey('sep')->values;
		}

		$this->_value = implode($sep, (array) $strings);
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_STR_OBJ;
	}

	public function __toString(): string {
		return "{$this->_value}";
	}
}
