<?php

namespace spaf\simputils;

use spaf\simputils\attributes\markers\Shortcut;

/**
 * Math and common calculations functionality
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
 * NOTE mt_* methods are not shortcut, because supposedly starting from 7.1 PHP version
 *      the equivalents without "mt_" prefix became aliases to the "mt_" prefixed functionality.
 *      If you have confirmation that it's not like that - please create an issue.
 *      In any case all of those "random" generators like `rand()` and `mt_rand()` are unsafe to
 *      be uses in cryptographical purposes, and should not be used for those purposes.
 *
 * @see https://www.php.net/manual/en/ref.math.php
 */
class Math {

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
	#[Shortcut('\base_convert()')]
	static function baseConvert(string $num, int $from_base, int $to_base): string {
		return base_convert($num, $from_base, $to_base);
	}

	/**
	 * Binary to decimal
	 *
	 * Returns the decimal equivalent of the binary number represented by the
	 * binary_string argument.
	 * `bin2dec()` converts a binary number to an int or, if needed for size reasons, float.
	 * `bin2dec()` interprets all binary_string values as unsigned integers. This is because
	 * `bin2dec()` sees the most significant bit as another order of magnitude rather than as
	 * the sign bit.
	 *
	 * IMP  Was renamed to be consistent in naming, PHP is having nightmare in matter of naming
	 *      and/or namespacing... So any conversion of two units would be named through "2" symbol
	 *
	 * **Important:** The parameter must be a string. Using other data types will produce
	 * unexpected results.
	 *
	 * @param string $binary_string The binary string to convert. Any invalid characters
	 *                              in binary_string are silently ignored. As of PHP 7.4.0
	 *                              supplying any invalid characters is deprecated.
	 *
	 * @see https://www.php.net/manual/en/function.bindec.php
	 * @return int|float The decimal value of binary_string
	 */
	#[Shortcut('\bindec()')]
	static function bin2dec(string $binary_string): int|float {
		return bindec($binary_string);
	}

	/**
	 * Round fractions up
	 *
	 * Returns the next highest integer value by rounding up num if necessary.
	 *
	 * @param int|float $num The value to round
	 *
	 * @see https://www.php.net/manual/en/function.ceil.php
	 * @return float num rounded up to the next highest integer. The return value of ceil() is
	 *               still of type float as the value range of float is usually bigger than that
	 *               of int.
	 */
	#[Shortcut('\ceil()')]
	static function ceil(int|float $num): float {
		return ceil($num);
	}

	/**
	 * Cosine
	 *
	 * cos() returns the cosine of the num parameter. The num parameter is in radians.
	 *
	 * @param float $num An angle in radians
	 *
	 * @see https://www.php.net/manual/en/function.cos.php
	 * @return float The cosine of num
	 */
	#[Shortcut('\cos()')]
	static function cos(float $num): float {
		return cos($num);
	}

	/**
	 * Hyperbolic cosine
	 *
	 * Returns the hyperbolic cosine of num, defined as (exp(arg) + exp(-arg))/2.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.cosh.php
	 * @return float The hyperbolic cosine of num
	 */
	static function cosh(float $num): float {
		return cosh($num);
	}

	/**
	 * Decimal to binary
	 *
	 * Returns a string containing a binary representation of the given num argument.
	 *
	 * IMP  Was renamed to be consistent in naming, PHP is having nightmare in matter of naming
	 *      and/or namespacing... So any conversion of two units would be named through "2" symbol
	 *
	 * @param int $num Decimal value to convert
	 *
	 * @see https://www.php.net/manual/en/function.decbin.php
	 * @return string Binary string representation of num
	 */
	#[Shortcut('\decbin()')]
	static function dec2bin(int $num): string {
		return decbin($num);
	}

	/**
	 * Decimal to hexadecimal
	 *
	 * Returns a string containing a hexadecimal representation of the given unsigned num argument.
	 *
	 * The largest number that can be converted is PHP_INT_MAX * 2 + 1 (or -1): on 32-bit platforms,
	 * this will be 4294967295 in decimal, which results in dechex() returning ffffffff.
	 *
	 * IMP  Was renamed to be consistent in naming, PHP is having nightmare in matter of naming
	 *      and/or namespacing... So any conversion of two units would be named through "2" symbol
	 *
	 * @param int $num The decimal value to convert.
	 *                 As PHP's int type is signed, but dechex() deals with unsigned integers,
	 *                 negative integers will be treated as though they were unsigned.
	 *
	 * @see https://www.php.net/manual/en/function.dechex.php
	 * @return string Hexadecimal string representation of num.
	 */
	#[Shortcut('\dechex()')]
	static function dec2hex(int $num): string {
		return dechex($num);
	}

	/**
	 * Decimal to octal
	 *
	 * Returns a string containing an octal representation of the given num argument.
	 * The largest number that can be converted depends on the platform in use. For 32-bit platforms
	 * this is usually 4294967295 in decimal resulting in 37777777777. For 64-bit platforms this is
	 * usually 9223372036854775807 in decimal resulting in 777777777777777777777.
	 *
	 * IMP  Was renamed to be consistent in naming, PHP is having nightmare in matter of naming
	 *      and/or namespacing... So any conversion of two units would be named through "2" symbol
	 *
	 * @param int $num Decimal value to convert
	 *
	 * @see https://www.php.net/manual/en/function.decoct.php
	 * @return string Octal string representation of num
	 */
	#[Shortcut('\decoct()')]
	static function dec2oct(int $num): string {
		return decoct($num);
	}

	/**
	 * Converts the number in degrees to the radian equivalent
	 *
	 * This function converts num from degrees to the radian equivalent.
	 *
	 * @param float $num Angular value in degrees
	 *
	 * @see https://www.php.net/manual/en/function.deg2rad.php
	 * @return float The radian equivalent of num
	 */
	#[Shortcut('\deg2rad()')]
	static function deg2rad(float $num): float {
		return deg2rad($num);
	}

	/**
	 * Calculates the exponent of **e**
	 *
	 * Returns e raised to the power of num.
	 *
	 * **Important:** 'e' is the base of the natural system of logarithms,
	 * or approximately 2.718282.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.exp.php
	 * @return float 'e' raised to the power of num
	 */
	#[Shortcut('\exp()')]
	static function exp(float $num): float {
		return exp($num);
	}

	/**
	 * Returns exp(number) - 1, computed in a way that is accurate even when the value of
	 * number is close to zero
	 *
	 * expm1() returns the equivalent to 'exp(num) - 1' computed in a way that is accurate
	 * even if the value of num is near zero, a case where 'exp (num) - 1' would be inaccurate
	 * due to subtraction of two numbers that are nearly equal.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.expm1.php
	 * @return float 'e' to the power of num minus one
	 */
	#[Shortcut('\expm1()')]
	static function expm1(float $num): float {
		return expm1($num);
	}

	/**
	 * Divides two numbers, according to IEEE 754
	 *
	 * Returns the floating point result of dividing the num1 by the num2. If the num2 is zero,
	 * then one of INF, -INF, or NAN will be returned.
	 *
	 * Note that in comparisons, NAN will never == or ===, any value, including itself.
	 *
	 * @param float $num1 The dividend (numerator)
	 * @param float $num2 The divisor
	 *
	 * @see https://www.php.net/manual/en/function.fdiv.php
	 * @return float The floating point result of num1/num2
	 */
	#[Shortcut('\fdiv()')]
	static function fdiv(float $num1, float $num2): float {
		return fdiv($num1, $num2);
	}

	/**
	 * Round fractions down
	 *
	 * Returns the next lowest integer value (as float) by rounding down num if necessary.
	 *
	 * @param int|float $num The numeric value to round
	 *
	 * @see https://www.php.net/manual/en/function.floor.php
	 * @return float num rounded to the next lowest integer. The return value of floor() is still
	 *               of type float because the value range of float is usually bigger than that
	 *               of int. This function returns false in case of an error
	 *               (e.g. passing an array).
	 */
	#[Shortcut('\floor()')]
	static function floor(int|float $num): float {
		return floor($num);
	}

	/**
	 * Returns the floating point remainder (modulo) of the division of the arguments
	 *
	 * Returns the floating point remainder of dividing the dividend (num1) by the divisor (num2).
	 * The remainder (r) is defined as: num1 = i * num2 + r, for some integer i.
	 * If num2 is non-zero, r has the same sign as num1 and a magnitude less than the magnitude
	 * of num2.
	 *
	 * @param float $num1 The dividend
	 * @param float $num2 The divisor
	 *
	 * @see https://www.php.net/manual/en/function.fmod.php
	 * @return float The floating point remainder of num1/num2
	 */
	#[Shortcut('\fmod()')]
	static function fmod(float $num1, float $num2): float {
		return fmod($num1, $num2);
	}

	/**
	 * Returns array with 2 values: [quotient, remainder]
	 *
	 * Basically uses `Math::intdiv()` + `Math::fmod()`
	 *
	 * Usage example:
	 * ```php
	 *      use \spaf\simputils\Math;
	 *      [$quotient, $remainder] = Math::divmod(9, 2);
	 * ```
	 *
	 * Result would be `$quotient` equals 4 and `$remainder` equals 1
	 *
	 * @param float $dividend Dividend
	 * @param float $divisor  Divisor
	 *
	 * @return array Returns [quotient, remainder]
	 */
	static function divmod(float $dividend, float $divisor): array {
		return [
			static::intdiv($dividend, $divisor),
			static::fmod($dividend, $divisor)
		];
	}

	/**
	 * Show largest possible random value
	 *
	 * Returns the maximum value that can be returned by a call to `Math::rand()`/`rand()`.
	 *
	 * @see https://www.php.net/manual/en/function.getrandmax.php
	 * @return int The largest possible random value returned by rand()
	 */
	#[Shortcut('\getrandmax()')]
	static function getRandMax(): int {
		return getrandmax();
	}

	/**
	 * Hexadecimal to decimal
	 *
	 * Returns the decimal equivalent of the hexadecimal number represented by the hex_string
	 * argument. `Math::hex2dec()` converts a hexadecimal string to a decimal number.
	 *
	 * `Math::hex2dec()` will ignore any non-hexadecimal characters it encounters.
	 * As of PHP 7.4.0 supplying any invalid characters is deprecated.
	 *
	 * @param string $hex_string The hexadecimal string to convert
	 *
	 * @return int|float The decimal representation of hex_string
	 */
	#[Shortcut('\hexdec()')]
	static function hex2dec(string $hex_string): int|float {
		return hexdec($hex_string);
	}

	/**
	 * Calculate the length of the hypotenuse of a right-angle triangle
	 *
	 * hypot() returns the length of the hypotenuse of a right-angle triangle with sides
	 * of length x and y, or the distance of the point (x, y) from the origin.
	 *
	 * This is equivalent to sqrt(x*x + y*y).
	 *
	 * @param float $x Length of first side
	 * @param float $y Length of second side
	 *
	 * @see https://www.php.net/manual/en/function.hypot.php
	 * @return float Calculated length of the hypotenuse
	 */
	#[Shortcut('\hypot()')]
	static function hypot(float $x, float $y): float {
		return hypot($x, $y);
	}

	/**
	 * Integer division
	 *
	 * Returns the integer quotient of the division of num1 by num2.
	 *
	 * @param int $num1 Number to be divided
	 * @param int $num2 Number which divides the num1
	 *
	 * @throws \DivisionByZeroError If num2 is 0, a DivisionByZeroError exception is thrown.
	 * @throws \ArithmeticError     If the num1 is PHP_INT_MIN and the num2 is -1,
	 *                              then an ArithmeticError exception is thrown.
	 *
	 * @see https://www.php.net/manual/en/function.intdiv.php
	 * @return int The integer quotient of the division of num1 by num2
	 */
	#[Shortcut('\intdiv()')]
	static function intdiv(int $num1, int $num2): int {
		return intdiv($num1, $num2);
	}

	/**
	 * Finds whether a value is a legal finite number
	 *
	 * Checks whether num is a legal finite on this platform.
	 *
	 * @param float $num The value to check
	 *
	 * @see https://www.php.net/manual/en/function.is-finite.php
	 * @return bool true if num is a legal finite number within the allowed range for a PHP float
	 *              on this platform, else false.
	 */
	#[Shortcut('\is_finite()')]
	static function isFinite(float $num): bool {
		return is_finite($num);
	}

	/**
	 * Finds whether a value is infinite
	 *
	 * Returns true if num is infinite (positive or negative), like the result of log(0) or
	 * any value too big to fit into a float on this platform.
	 *
	 * @param float $num The value to check
	 *
	 * @see https://www.php.net/manual/en/function.is-infinite.php
	 * @return bool true if num is infinite, else false.
	 */
	#[Shortcut('\is_infinite()')]
	static function isInfinite(float $num): bool {
		return is_infinite($num);
	}

	/**
	 * Finds whether a value is not a number
	 *
	 * Checks whether num is 'not a number', like the result of acos(1.01).
	 *
	 * @param float $num The value to check
	 *
	 * @see https://www.php.net/manual/en/function.is-nan.php
	 * @return bool Returns true if num is 'not a number', else false.
	 */
	#[Shortcut('\is_nan()')]
	static function isNan(float $num): bool {
		return is_nan($num);
	}

	/**
	 * Combined linear congruential generator
	 *
	 * `Math::lcgValue()` and `lcg_value()` returns a pseudo random number in the range of (0, 1).
	 * The function combines two CGs with periods of 2^31 - 85 and 2^31 - 249.
	 * The period of this function is equal to the product of both primes.
	 *
	 * **Important:** This function does not generate cryptographically secure values, and
	 * should not be used for cryptographic purposes.
	 * If you need a cryptographically secure value, consider using
	 * `random_int()`, `random_bytes()`, or `openssl_random_pseudo_bytes()` instead.
	 *
	 * @see https://www.php.net/manual/en/function.lcg-value.php
	 * @return float A pseudo random float value between 0.0 and 1.0, inclusive.
	 */
	#[Shortcut('\lcg_value()')]
	static function lcgValue(): float {
		return lcg_value();
	}

	/**
	 * Base-10 logarithm
	 *
	 * Returns the base-10 logarithm of num.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.log10.php
	 * @return float The base-10 logarithm of num
	 */
	#[Shortcut('\log10()')]
	static function log10(float $num): float {
		return log10($num);
	}

	/**
	 * Returns log(1 + number), computed in a way that is accurate even when the value
	 * of number is close to zero
	 *
	 * `log1p()` returns log(1 + num) computed in a way that is accurate even when the value
	 * of num is close to zero. `log()` might only return log(1) in this case due to lack
	 * of precision.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.log1p.php
	 * @return float log(1 + num)
	 */
	#[Shortcut('\log1p()')]
	static function log1p(float $num): float {
		return log1p($num);
	}

	/**
	 * Natural logarithm
	 *
	 * If the optional base parameter is specified, log() returns logbase num,
	 * otherwise log() returns the natural logarithm of num.
	 *
	 * @param float $num  The value to calculate the logarithm for
	 * @param float $base The optional logarithmic base to use (defaults to 'e' and so
	 *                    to the natural logarithm).
	 *
	 * @see https://www.php.net/manual/en/function.log.php
	 * @return float The logarithm of num to base, if given, or the natural logarithm.
	 */
	#[Shortcut('\log()')]
	static function log(float $num, float $base = M_E): float {
		return log($num, $base);
	}

	/**
	 * Find highest value
	 *
	 * If the first and only parameter is an array, max() returns the highest value in that array.
	 * If at least two parameters are provided, max() returns the biggest of these values.
	 *
	 * NOTE For consistency purpose, recommended to use the main signature format, and avoid using
	 *      alternative format (single array of elements).
	 *
	 * **Important:** Values of different types will be compared using the standard comparison
	 * rules. For instance, a non-numeric string will be compared to an int as though it were 0,
	 * but multiple non-numeric string values will be compared alphanumerically.
	 * The actual value returned will be of the original type with no conversion applied.
	 *
	 * **Important:** Be careful when passing arguments of different types because `Math::max()`
	 * can produce unpredictable results.
	 *
	 * @param mixed $value     Any comparable value.
	 * @param mixed ...$values Any comparable values.
	 *
	 * @see https://www.php.net/manual/en/function.max.php
	 * @return mixed Returns the parameter value considered "highest" according to standard
	 *               comparisons.
	 *               If multiple values of different types evaluate as equal (e.g. 0 and 'abc')
	 *               the first provided to the function will be returned.
	 *               If an empty array is passed, then false will be returned and
	 *               an E_WARNING error will be emitted.
	 */
	#[Shortcut('\max()')]
	static function max(mixed $value, mixed ...$values): mixed {
		return max($value, ...$values);
	}

	/**
	 * Find lowest value
	 *
	 * If the first and only parameter is an array, `Math::min()` returns the lowest value in
	 * that array.
	 * If at least two parameters are provided, `Math::min()` returns the smallest of these values.
	 *
	 * NOTE For consistency purpose, recommended to use the main signature format, and avoid using
	 *      alternative format (single array of elements).
	 *
	 * **Important:** Values of different types will be compared using the standard
	 * comparison rules. For instance, a non-numeric string will be compared to an int as though
	 * it were 0, but multiple non-numeric string values will be compared alphanumerically.
	 * The actual value returned will be of the original type with no conversion applied.
	 *
	 * **Important:** Be careful when passing arguments of different types because `Math::min()`
	 * can produce unpredictable results.
	 *
	 * @param mixed $value     Any comparable value.
	 * @param mixed ...$values Any comparable values.
	 *
	 * @see https://www.php.net/manual/en/function.min.php
	 * @return mixed Returns the parameter value considered "lowest" according to standard
	 *               comparisons. If multiple values of different types evaluate as equal
	 *               (e.g. 0 and 'abc') the first provided to the function will be returned.
	 *               If an empty array is passed, then false will be returned and
	 *               an E_WARNING error will be emitted.
	 */
	#[Shortcut('\min()')]
	static function min(mixed $value, mixed ...$values): mixed {
		return min($value, ...$values);
	}

	/**
	 * Octal to decimal
	 *
	 * Returns the decimal equivalent of the octal number represented by the octal_string argument.
	 *
	 * @param string $octal_string The octal string to convert. Any invalid characters
	 *                             in octal_string are silently ignored. As of PHP 7.4.0 supplying
	 *                             any invalid characters is deprecated.
	 *
	 * @see https://www.php.net/manual/en/function.octdec.php
	 * @return int|float The decimal representation of octal_string
	 */
	#[Shortcut('\octdec()')]
	static function oct2dec(string $octal_string): int|float {
		return octdec($octal_string);
	}

	/**
	 * Get value of pi
	 *
	 * Returns an approximation of pi. Also, you can use the M_PI constant which
	 * yields identical results to pi().
	 *
	 * @see https://www.php.net/manual/en/function.pi.php
	 * @return float The value of pi as float.
	 */
	#[Shortcut('\pi()')]
	static function pi(): float {
		return pi();
	}

	/**
	 * Exponential expression
	 *
	 * Returns num raised to the power of exponent.
	 *
	 * **Important:** It is possible to use the ** operator instead.
	 *
	 * @param mixed $num      The base to use
	 * @param mixed $exponent The exponent
	 *
	 * @see https://www.php.net/manual/en/function.pow.php
	 * @return int|float|object num raised to the power of exponent. If both arguments are
	 *                          non-negative integers and the result can be represented as
	 *                          an integer, the result will be returned with int type,
	 *                          otherwise it will be returned as a float.
	 */
	#[Shortcut('\pow()')]
	static function pow(mixed $num, mixed $exponent): int|float|object {
		return pow($num, $exponent);
	}

	/**
	 * Converts the radian number to the equivalent number in degrees
	 *
	 * This function converts num from radian to degrees
	 *
	 * @param float $num A radian value
	 *
	 * @see https://www.php.net/manual/en/function.rad2deg.php
	 * @return float The equivalent of num in degrees
	 */
	#[Shortcut('\rad2deg()')]
	static function rad2deg(float $num): float {
		return rad2deg($num);
	}

	/**
	 * Generate a random integer
	 *
	 * If called without the optional min, max arguments `Math::rand()` returns a pseudo-random
	 * integer between 0 and `Math::getRandMax()`.
	 * If you want a random number between 5 and 15 (inclusive),
	 * for example, use `Math::rand(5, 15)`
	 *
	 * **Important:** In this shortcut, both parameters are absolutely optional, so you can
	 * define $min or $max or both or none. This is the only difference from the standard `rand()`
	 *
	 * **Important:** This function does not generate cryptographically secure values, and
	 * should not be used for cryptographic purposes.
	 * If you need a cryptographically secure value, consider using random_int(), random_bytes(),
	 * or openssl_random_pseudo_bytes() instead.
	 *
	 * IMP  Even though it's still a shortcut, there are some small adjustments are done
	 *      for more comfortable use (optional params)
	 *
	 * @param int  $min The lowest value to return (default: 0)
	 * @param ?int $max The highest value to return (default: `Math::getRandMax()`)
	 *
	 * @see https://www.php.net/manual/en/function.rand.php
	 * @return int A pseudo random value between min (or 0) and
	 *             max (or `Math::getRandMax()`, inclusive).
	 */
	#[Shortcut('\rand()')]
	static function rand(int $min = 0, ?int $max = null) {
		if (is_null($max)) {
			$max = static::getRandMax();
		}
		return rand($min, $max);
	}

	/**
	 * Rounds a float
	 *
	 * Returns the rounded value of num to specified precision (number of digits after
	 * the decimal point). precision can also be negative or zero (default).
	 *
	 * @param int|float $num       The value to round.
	 * @param int       $precision The optional number of decimal digits to round to.
	 *                             If the precision is positive, num is rounded to precision
	 *                             significant digits after the decimal point.
	 *                             If the precision is negative, num is rounded to precision
	 *                             significant digits before the decimal point, i.e.
	 *                             to the nearest multiple of pow(10, -precision), e.g.
	 *                             for a precision of -1 num is rounded to tens,
	 *                             for a precision of -2 to hundreds, etc.
	 * @param int       $mode      Use one of the following constants to specify the mode in which
	 *                             rounding occurs.
	 *
	 * @see https://www.php.net/manual/en/function.round.php
	 * @return float The value rounded to the given precision as a float.
	 */
	#[Shortcut('\round()')]
	static function round(
		int|float $num,
		int $precision = 0,
		int $mode = PHP_ROUND_HALF_UP
	): float {
		return round($num, $precision, $mode);
	}

	/**
	 * Sine
	 *
	 * sin() returns the sine of the num parameter. The num parameter is in radians.
	 *
	 * @param float $num A value in radians
	 *
	 * @see https://www.php.net/manual/en/function.sin.php
	 * @return float The sine of num
	 */
	#[Shortcut('\sin()')]
	static function sin(float $num): float {
		return sin($num);
	}

	/**
	 * Hyperbolic sine
	 *
	 * Returns the hyperbolic sine of num, defined as (exp(num) - exp(-num))/2.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.sinh.php
	 * @return float The hyperbolic sine of num
	 */
	#[Shortcut('\sinh()')]
	static function sinh(float $num): float {
		return sinh($num);
	}

	/**
	 * Square root
	 *
	 * Returns the square root of num.
	 *
	 * @param float $num The argument to process
	 *
	 * @see https://www.php.net/manual/en/function.sqrt.php
	 * @return float The square root of num or the special value NAN for negative numbers.
	 */
	#[Shortcut('\sqrt()')]
	static function sqrt(float $num): float {
		return sqrt($num);
	}

	/**
	 * Seed the random number generator
	 *
	 * Seeds the random number generator with seed or with a random value if seed is 0.
	 *
	 * **Important:** There is no need to seed the random number generator with `Math::srand()` or
	 * `mt_srand()` as this is done automatically.
	 *
	 * @param int $seed An arbitrary int seed value.
	 * @param int $mode MT_RAND_MT19937
	 *
	 * @see https://www.php.net/manual/en/function.srand.php
	 * @return void
	 */
	#[Shortcut('\srand()')]
	static function srand(int $seed = 0, int $mode = MT_RAND_MT19937): void {
		srand($seed, $mode);
	}

	/**
	 * Tangent
	 *
	 * Returns the tangent of the num parameter. The num parameter is in radians.
	 *
	 * @param float $num The argument to process in radians
	 *
	 * @see https://www.php.net/manual/en/function.tan.php
	 * @return float The tangent of num
	 */
	#[Shortcut('\tan()')]
	static function tan(float $num): float {
		return tan($num);
	}

	/**
	 * Hyperbolic tangent
	 *
	 * Returns the hyperbolic tangent of num, defined as sinh(num)/cosh(num).
	 *
	 * @param float $num Returns the hyperbolic tangent of num, defined as sinh(num)/cosh(num).
	 *
	 * @see https://www.php.net/manual/en/function.tanh.php
	 * @return float The hyperbolic tangent of num
	 */
	#[Shortcut('\tanh()')]
	static function tanh(float $num): float {
		return tanh($num);
	}
}
