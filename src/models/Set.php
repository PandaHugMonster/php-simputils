<?php

namespace spaf\simputils\models;

use spaf\simputils\PHP;
use spaf\simputils\traits\RedefinableComponentTrait;

class Set extends Box {
	use RedefinableComponentTrait;

	protected $_map_keys;
	protected $_map_values;

	public function __construct(
		array|Box $array = [],
		int $flags = 0,
		string $iteratorClass = "ArrayIterator"
	) {
		if ($array instanceof Box) {
			$array = $array->clone(); // @codeCoverageIgnore
		}
		$array = $this->cleanUpAndCache(PHP::box($array));
		parent::__construct($array, $flags, $iteratorClass);
	}

	protected function cleanUpAndCache(Box $array): Box {
		$accu = PHP::box();
		$array = $array->each(function ($value, $key) use (&$accu) {
			if ($accu->containsValue($value)) {
				return null;
			}
			$accu[] = $value;
			return [$value, $key];
		});

		return $array;
	}

	public function offsetSet(mixed $key, mixed $value): void {
		if (!$this->containsValue($value)) {
			parent::offsetSet($key, $value); // TODO: Change the autogenerated stub
		}
	}

	public function exchangeArray(array|object $array): array {
		$array = PHP::box($array);
		return parent::exchangeArray($this->cleanUpAndCache($array));
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_SET;
	}
}