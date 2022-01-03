<?php

namespace spaf\simputils;

use spaf\simputils\attributes\markers\Shortcut;
use function strlen;

/**
 *
 * Due to some significantly outdated limitations of PHP, it's too overcomplicated to have a native
 * String class. So this class will remain static as `Math` and `PHP`
 *
 */
class Str {

	/**
	 * Shortcut for `\sprintf()`
	 *
	 * Named like this for fun and to resolve some collision of namings in PHP.
	 * Fun part `Str::ing()`:
	 * ```php
	 *      use spaf\simputils\Str;
	 *      $str = Str::ing('My string');
	 * ```
	 *
	 * TODO Consider implementing dummy-protection against incomplete/overwhelmed pattern
	 *      fulfilling (maybe?)
	 *
	 * @param string $str       String or any value, or pattern for sprintf
	 * @param string ...$params Params for the sprintf pattern
	 *
	 * @return string
	 */
	public static function ing(string $str, string ...$params): string {
		return sprintf($str, ...$params);
	}

	/**
	 * Pseudonym of `static::ing()`
	 *
	 * @param string $str       String or any value, or pattern for sprintf
	 * @param string ...$params Params for the sprintf pattern
	 *
	 * @return string
	 */
	#[Shortcut('static::ing()')]
	public static function get(string $str, string ...$params): string {
		return static::ing($str, ...$params);
	}

	/**
	 * Turns value of any type to string
	 *
	 * For boolean:
	 *      Turn bool true or false into string "true" or "false"
	 *      Opposite functionality of {@see \spaf\simputils\Boolean::from()}.
	 *
	 * IMP  It recognizes different between 1 and true, so if you want "bool" representation string,
	 *      provide bool-value, and not the integer one
	 *
	 * @param mixed $value Value to convert
	 *
	 * TODO Improve further...
	 * @return string|null
	 *@see \spaf\simputils\Boolean::from()
	 */
	public static function from(mixed $value): ?string {
		if ($value === true || $value === false) {
			return $value ?'true':'false';
		}

		return "$value";
	}

	/**
	 * @param string $var Target string
	 *
	 * @return int
	 */
	#[Shortcut('\strlen()')]
	public static function len(string $var) {
		return strlen($var);
	}

	/**
	 * Quick uuid solution
	 *
	 * @see Uuid
	 *
	 * @codeCoverageIgnore
	 * @return string
	 */
	public static function uuid(): string {
		throw new NotImplementedYet();
	}

	/**
	 * Check if a string is JSON parsable
	 *
	 * @param string $json_or_not String to check
	 *
	 * @return bool
	 */
	public static function isJson(string $json_or_not): bool {
		json_decode($json_or_not, true);
		if (json_last_error() === JSON_ERROR_NONE)
			return true;
		return false;
	}
}
