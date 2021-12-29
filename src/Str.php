<?php

namespace spaf\simputils;

use spaf\simputils\attributes\markers\Shortcut;

/**
 *
 * Due to some significantly outdated limitations of PHP, it's too overcomplicated to have a native
 * String class. So this class will remain static as `Math` and `PHP`
 *
 * FIX  Unfinished. Proceed after DotEnv
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
}
