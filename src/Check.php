<?php

namespace spaf\simputils;

use spaf\simputils\attributes\markers\Shortcut;
use Stringable;
use function is_bool;
use function is_numeric;
use function is_string;
use function method_exists;

/**
 * Helper class to perform common checks
 *
 * Like "isString" but a bit more advanced than standard PHP implementation.
 *
 * Additionally features for checking against MetaMagic "casting" would be added later
 *
 */
class Check {

	/**
	 * Checking if `$target` argument is a string/string-alike or not
	 *
	 *
	 * @param mixed $target             Any type of value
	 * @param bool  $is_strict          If set to true, checks only against `\is_string()` and
	 *                                  `Stringable`
	 * @param bool  $include_primitives Whether to consider `bool`, `int` and `float` as strings
	 *                                  This will be ignored if `$is_strict` set to true
	 *
	 * @return bool
	 */
	#[Shortcut(
		"\is_string()",
		"Shortcut is not exact, this method far more richer in functionality " .
		"than the standard PHP implementation"
	)]
	static function isString(
		mixed $target,
		bool  $is_strict = false,
		bool  $include_primitives = false,
	): bool {
		// MARK Implement `NonString` marker for classes,
		//      to forcefully mark classes as "non-strings"

		// NOTE Checking against standard PHP entities
		if (is_string($target) || $target instanceof Stringable) {
			return true;
		}

		// NOTE If standard PHP entities failed and in a strict mode, then return false
		if ($is_strict) {
			return false;
		}

		// NOTE When primitives allowed, checking against those types
		if ($include_primitives && (is_numeric($target) || is_bool($target))) {
			return true;
		}

		if (method_exists($target, "__toString")) {
			return true;
		}
		// TODO Add checks for new features of renewed MetaMagic when implemented

		return false;
	}

}
