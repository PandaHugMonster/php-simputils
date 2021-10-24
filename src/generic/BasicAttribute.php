<?php

namespace spaf\simputils\generic;

use Attribute;
use Exception;
use spaf\simputils\PHP;
use function in_array;
use function is_object;

#[Attribute]
abstract class BasicAttribute {

	public mixed $target_parent = null;
	public mixed $target_parent_ref = null;
	public mixed $target_ref = null;
	public ?string $target_name = null;
	public ?string $target_type = null;
	public ?string $target_representation = null;

	private function runBasic($params): mixed {
		if (empty($this->target_name) || empty($this->target_type)) {
			throw new Exception('Attribute\'s "$context" field must always be provided');
		}

		return $this->run(...$params);
	}

	public function run(): mixed {
		return null;
	}

	public function __invoke(...$params): mixed {
		$functions = [Attribute::TARGET_METHOD, Attribute::TARGET_FUNCTION];
		if (in_array($this->target_type, $functions)) {
			$parent_str = $this->target_parent;
			if (is_object($parent_str)) {
				$parent_str = PHP::type($parent_str);
			}
			$this->target_representation = $parent_str.'::'.$this->target_name.'()';
		}
		return $this->runBasic($params);
	}

	public function setup(...$params): static {
		foreach ($params as $key => $val) {
			$this->$key = $val;
		}

		return $this;
	}
}
