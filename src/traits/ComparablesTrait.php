<?php

namespace spaf\simputils\traits;

trait ComparablesTrait {

	abstract function equalsTo(...$args): bool;

	abstract function greaterThan(...$args): bool;

	abstract function lessThan(...$args): bool;

	function greaterThanEqual(...$args): bool {
		return $this->equalsTo(...$args) || $this->greaterThan(...$args);
	}

	function lessThanEqual(...$args): bool {
		return $this->equalsTo(...$args) || $this->lessThan(...$args);
	}

	function e(...$args): bool {
		return $this->equalsTo(...$args);
	}

	function gt(...$args): bool {
		return $this->greaterThan(...$args);
	}

	function lt(...$args): bool {
		return $this->lessThan(...$args);
	}

	function gte(...$args): bool {
		return $this->greaterThanEqual(...$args);
	}

	function lte(...$args): bool {
		return $this->lessThanEqual(...$args);
	}
}
