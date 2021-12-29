<?php

namespace spaf\simputils;

use spaf\simputils\attributes\markers\Shortcut;

/**
 * Math and common calculations functionality
 *
 * FIX  Unfinished. Proceed after DotEnv!
 *
 * IMP  The most of the documentation for shortcut functions is purely copied from PHP.net
 *      For some other methods that were adjusted or create from scratch description
 *      is done by Panda.
 *
 * IMP  The direct call is ~2 times quicker than the shortcut for 1_000_000 calls of abs()
 *      t1: 0.50994801521301    - Cost of **shortcut calls** of `abs()` (**statically-defined**)
 *      t2: 0.2618579864502     - Cost of **direct calls** of `abs()`
 *      t3: 0.27749609947205    - Cost of **simple piping** through (**statically-defined**)
 *      t4: 0.57750296592712    - Cost of **simple piping** through (**dynamically-defined**)
 *      t5: 0.7862229347229     - Cost of **shortcut calls** of `abs()` (**dynamically-defined**)
 *      So with those rough estimations there is no significant difference, or the difference
 *      could be neglected!
 *
 * @see https://www.php.net/manual/en/ref.math.php
 */
class Math {

	//// Basic Math Functions

	/**
	 * Absolute value
	 *
	 * Returns the absolute value of num.
	 *
	 * @param int|float $num The numeric value to process
	 *
	 * @see https://www.php.net/manual/en/function.abs.php
	 * @return int|float
	 */
	#[Shortcut('\abs()')]
	static function abs(int|float $num): int|float {
		return abs($num);
	}

	/**
	 * Arc cosine
	 *
	 * Returns the arc cosine of num in radians. `acos()` is the inverse function of `cos()`,
	 * which means that `a==cos(acos(a))` for every value of a that is within `acos()`' range.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.acos.php
	 * @return float The arc cosine of num in radians.
	 */
	#[Shortcut('\acos()')]
	static function acos(float $num): float {
		return acos($num);
	}

	/**
	 * Inverse hyperbolic cosine
	 *
	 * Returns the inverse hyperbolic cosine of num, i.e. the value whose hyperbolic cosine is num.
	 *
	 * @param float $num The value to process
	 *
	 * @see https://www.php.net/manual/en/function.acosh.php
	 * @return float The inverse hyperbolic cosine of num
	 */
	#[Shortcut('\acosh()')]
	static function acosh(float $num): float {
		return acosh($num);
	}

	/**
	 * Arc sine
	 *
	 * Returns the arc sine of num in radians. `asin()` is the inverse function of `sin()`,
	 * which means that `a==sin(asin(a))` for every value of a that is within `asin()`'s range.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.asin.php
	 * @return float The arc sine of num in radians
	 */
	#[Shortcut('\asin()')]
	static function asin(float $num): float {
		return asin($num);
	}

	/**
	 * Inverse hyperbolic sine
	 *
	 * Returns the inverse hyperbolic sine of num, i.e. the value whose hyperbolic sine is num.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.asinh.php
	 * @return float The inverse hyperbolic sine of num
	 */
	#[Shortcut('\asinh()')]
	static function asinh(float $num): float {
		return asinh($num);
	}

	/**
	 * Arc tangent of two variables
	 *
	 * This function calculates the arc tangent of the two variables x and y.
	 * It is similar to calculating the arc tangent of y / x, except that the signs of both
	 * arguments are used to determine the quadrant of the result.
	 *
	 * The function returns the result in radians, which is between -PI and PI (inclusive).
	 *
	 * @param float $y Dividend parameter
	 * @param float $x Divisor parameter
	 *
	 * @see https://www.php.net/manual/en/function.atan2.php
	 * @return float The arc tangent of y/x in radians.
	 */
	#[Shortcut('\atan2()')]
	static function atan2(float $y, float $x): float {
		return atan2($y, $x);
	}

	/**
	 * Arc tangent
	 *
	 * Returns the arc tangent of num in radians. `atan()` is the inverse function of `tan()`,
	 * which means that `a==tan(atan(a))` for every value of a that is within `atan()`'s range.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.atan.php
	 * @return float The arc tangent of num in radians.
	 */
	#[Shortcut('\atan()')]
	static function atan(float $num): float {
		return atan($num);
	}

	/**
	 * Inverse hyperbolic tangent
	 *
	 * Returns the inverse hyperbolic tangent of num, i.e. the value whose hyperbolic
	 * tangent is num.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.atanh.php
	 * @return float Inverse hyperbolic tangent of num
	 */
	#[Shortcut('\atanh()')]
	static function atanh(float $num): float {
		return atanh($num);
	}

	/**
	 * Convert a number between arbitrary bases
	 *
	 * Returns a string containing num represented in base to_base. The base in which num is given
	 * is specified in from_base. Both from_base and to_base have to be between 2 and 36, inclusive.
	 * Digits in numbers with a base higher than 10 will be represented with the letters a-z, with
	 * a meaning 10, b meaning 11 and z meaning 35. The case of the letters doesn't matter,
	 * i.e. num is interpreted case-insensitively.
	 *
	 * **Important:** `\base_convert()` may lose precision on large numbers due to properties
	 * related to the internal "double" or "float" type used.
	 * Please see the "Floating point numbers" section
	 * {@see https://www.php.net/manual/en/language.types.float.php} in the manual
	 * for more specific information and limitations.
	 *
	 * Example 1:
	 * ```php
	 *      use spaf\simputils\Math;
	 *
	 *      $hexadecimal = 'a37334';
	 *      echo Math::baseConvert($hexadecimal, 16, 2);
	 * ```
	 *
	 * @param string $num       The number to convert. Any invalid characters in num are silently
	 *                          ignored. As of PHP 7.4.0 supplying any invalid characters
	 *                          is deprecated.
	 * @param int    $from_base The base num is in
	 * @param int    $to_base   The base to convert num to
	 *
	 * @see https://www.php.net/manual/en/function.base-convert.php
	 * @return string num converted to base to_base
	 */
	#[Shortcut('\base_convert')]
	static function baseConvert(string $num, int $from_base, int $to_base): string {
		return base_convert($num, $from_base, $to_base);
	}

	// FIX  Proceed here after finishing DotEnv

	//
}
