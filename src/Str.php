<?php

namespace spaf\simputils;

use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\models\Box;
use function explode;
use function intval;
use function is_callable;
use function is_integer;
use function is_null;
use function is_numeric;
use function is_string;
use function mb_strlen;
use function mb_strpos;
use function str_ends_with;
use function str_starts_with;
use function substr;

/**
 *
 * Due to some significantly outdated limitations of PHP, it's too overcomplicated to have a native
 * String class. So this class will remain static as `Math` and `PHP`
 *
 * TODO Implement StrObj wrapper for the string, so the operations could be done in chain
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
	 * @see \spaf\simputils\Boolean::to()
	 */
	public static function from(mixed $value): ?string {
		if ($value === true || $value === false) {
			return Boolean::to($value);
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
	 * @param ?string $string Target string
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
	 * @return string
	 */
	public static function removeEnding(
		string $target,
		string|int $ending,
		bool $is_case_sensitive = true
	): string {
		if (is_integer($ending)) {
			if ($ending < 1) {
				return $target;
			}
			return substr($target, 0, -$ending);
		} else if (!$is_case_sensitive) {
			$ending = Str::upper($ending);
		}

		$len = static::len($ending);
		if ($len > 0 && static::endsWith($target, $ending, $is_case_sensitive)) {
			return substr($target, 0, -$len);
		}

		return $target;
	}

	public static function removeStarting(
		string $target,
		string|int $starting,
		bool   $is_case_sensitive = true
	): string {
		if (is_integer($starting)) {
			if ($starting < 1) {
				return $target;
			}
			return substr($target, $starting);
		} else if (!$is_case_sensitive) {
			$starting = Str::upper($starting);
		}

		$len = static::len($starting);
		if ($len > 0 && static::startsWith($target, $starting, $is_case_sensitive)) {
			return substr($target, $len);
		}

		return $target;
	}

	static function contains(
		string $target,
		string $sub_string,
		bool $is_case_sensitive = true
	): bool {
		if (!$is_case_sensitive) {
			$target = static::lower($target);
			$sub_string = static::lower($sub_string);
		}
		if (mb_strpos($target, $sub_string) === false) {
			return false;
		}

		return true;
	}

	#[Shortcut('static::contains()')]
	static function has(string $target, string $sub_string, bool $is_case_sensitive = true): bool {
		return static::contains($target, $sub_string, $is_case_sensitive);
	}

	/**
	 * Iterate through string as generator (for foreach)
	 *
	 * Basically you can't iterate through string "as an array", because it's not an array
	 * and is not being iterable. So for that purpose this method is needed.
	 *
	 * @param string $string String that should be walked through
	 *
	 * @return \Generator
	 */
	static function iter($string) {
		foreach (Math::range(0, static::len($string) - 1) as $i) {
			yield $string[$i];
		}
	}

	static function div($string, int $every): null|array|Box {
		$sub_res = '';
		$res = PHP::box();
		foreach (static::iter($string) as $i => $symbol) {
			$k = $i + 1;
			$sub_res .= $symbol;
			if ($k % $every === 0) {
				$res[] = $sub_res;
				$sub_res = '';
			}
		}

		return $res;
	}

	static function cut() {

	}

	/**
	 * @param string $str String to split
	 * @param string $sep Separator by which to split
	 *
	 * @return Box|array
	 * @see Box::implode()
	 * @see \explode()
	 */
	#[Shortcut('\explode()')]
	static function explode(string $str, string $sep = ', '): Box|array {
		return PHP::box(explode($sep, $str));
	}

	#[Shortcut('\explode()')]
	static function split(string $str, string $sep = ', '): Box|array {
		return static::explode($str, $sep);
	}

	/**
	 * Multiply (duplicate) string number of times
	 *
	 * @param string|callable $string String to repeat, or if it's callable - then
	 *                                this callable will be repeated for an every iteration
	 * @param int|string      $amount Amount of copies (by default is 1, so no copies).
	 *                                If string is not numeric supplied - it's length
	 *                                will be used (really commonly used in CLI UI)
	 *                                If negative supplied - then it's being turned into
	 *                                a positive number. If 0 is supplied, then return will
	 *                                be empty (empty string)
	 * @param string          $glue   The value in between each copy (by default is empty)
	 *
	 * @return string
	 */
	static function mul(
		string|callable $string,
		int|string $amount = 1,
		string $glue = ''
	): string {
		$res = '';
		if (is_numeric($amount)) {
			$amount = intval($amount);
		} else {
			$amount = static::len($amount);
		}
		if ($amount === 0) {
			return $res;
		}
		foreach (Math::range(1, Math::abs($amount)) as $i) {
			if (!empty($res)) {
				$res .= $glue;
			}
			if (is_callable($string)) {
				$res .= $string($i);
			} else {
				$res .= $string;
			}
		}

		return $res;
	}

	/**
	 * Duplicate (multiply) string number of times
	 *
	 * @param string|callable $string String to repeat, or if it's callable - then
	 *                                this callable will be repeated for an every iteration
	 * @param int|string      $amount Amount of copies (by default is 1, so no copies).
	 *                                If string is not numeric supplied - it's length
	 *                                will be used (really commonly used in CLI UI)
	 *                                If negative supplied - then it's being turned into
	 *                                a positive number. If 0 is supplied, then return will
	 *                                be empty (empty string)
	 * @param string          $glue   The value in between each copy (by default is empty)
	 *
	 * @return string
	 * @see \spaf\simputils\Str::mul()
	 */
	#[Shortcut('static::mul()')]
	static function dup(
		string|callable $string,
		int|string $amount = 1,
		string $glue = ''
	): string {
		return static::mul($string, $amount, $glue);
	}
}
