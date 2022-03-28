<?php

namespace spaf\simputils;

use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\models\StrObj;
use function is_integer;
use function is_null;
use function is_string;
use function mb_strlen;
use function str_ends_with;
use function str_starts_with;
use function substr;

/**
 *
 * Due to some significantly outdated limitations of PHP, it's too overcomplicated to have a native
 * String class. So this class will remain static as `Math` and `PHP`
 *
 * FIX  Implement StrObj wrapper for the string, so the operations could be done in chain
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
	 * TODO Add optional translations
	 * @return string|null
	 * @see \spaf\simputils\Boolean::from()
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
		return mb_strlen($var);
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

	/**
	 * Change all the letters to upper-case letters
	 *
	 * @param ?string $string $string Target string
	 *
	 * @return string
	 */
	#[Shortcut('\strtoupper()')]
	public static function upper(?string $string): string {
		return is_null($string)?'':mb_strtoupper($string);
	}

	/**
	 * Change all the letters to lower-case letters
	 *
	 * @param ?string $string Target string
	 *
	 * @return string
	 */
	#[Shortcut('\strtolower()')]
	public static function lower(?string $string): string {
		return is_null($string)?'':mb_strtolower($string);
	}

	/**
	 * Checks if the value is string
	 *
	 * @param mixed $val Target value
	 *
	 * @return bool True if the value is a string
	 */
	#[Shortcut('\is_string()')]
	public static function is(mixed $val): bool {
		return is_string($val);
	}

	#[Shortcut('\str_ends_with()')]
	public static function endsWith(
		string $str,
		string $expected_ending,
		bool $is_case_sensitive = true
	): bool {
		if (!$is_case_sensitive) {
			$str = static::upper($str);
			$expected_ending = static::upper($expected_ending);
		}
		return str_ends_with($str, $expected_ending);
	}

	#[Shortcut('\str_starts_with()')]
	public static function startsWith(
		string $str,
		string $expected_starting,
		bool   $is_case_sensitive = true
	): bool {
		if (!$is_case_sensitive) {
			$str = static::upper($str);
			$expected_starting = static::upper($expected_starting);
		}
		return str_starts_with($str, $expected_starting);
	}

	/**
	 * Remove ending of the string
	 *
	 * A few cases:
	 *  1.  Ending is a string - In this case it is being removed from the target
	 *      string if matches, otherwise ignoring
	 *  2.  Ending is an integer greater than 0 - then removes the amount of last symbols
	 *  3.  Any other cases suppose to be ignored
	 *
	 * @param string     $target The target string from which the second parameter could be removed
	 * @param string|int $ending The second parameter that specifies exact string to remove or
	 *                           amount of symbols to remove
	 *
	 * FIX  Implement urgently removeStarting in the same way
	 * @return string
	 */
	public static function removeEnding(string $target, string|int $ending): string {
		if (is_integer($ending)) {
			if ($ending < 1) {
				return $target;
			}
			return substr($target, 0, -$ending);
		}

		$len = static::len($ending);
		if ($len > 0 && static::endsWith($target, $ending)) {
			return substr($target, 0, -$len);
		}

		return $target;
	}

	public static function obj(string ...$strings): StrObj|string {
		$class_strobj = PHP::redef(StrObj::class);
		return new $class_strobj(...$strings);
	}
}
