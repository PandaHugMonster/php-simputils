<?php

namespace spaf\simputils;

class Boolean {

	public static array $array_yes = [
		'enabled', 'yes', 't', 'true', 'y', '+', '1', 'enable',
	];
	public static array $array_no = [
		'disabled', 'no', 'f', 'false', 'n', '-', '0', 'disable',
	];

	public static string $to_yes = 'true';
	public static string $to_no = 'false';

	/**
	 * Tries to recognize string or other types of value as bool TRUE or FALSE
	 *
	 * Decision is made based on {@see static::$array_yes}, what will not match, will be
	 * considered as FALSE, otherwise TRUE.
	 *
	 * Values can be modified, so you can control what should be considered as TRUE or FALSE
	 *
	 * If `$strict` supplied - then null will be returned if value does not match any of those 2
	 * arrays (yes and no).
	 *
	 * @param mixed $val    Converts string (or any) value into a bool value
	 *                      (recognition is based on $array_yes)
	 * @param bool  $strict If true - then null is returned if the value does not match both arrays
	 *
	 * @return ?bool
	 */
	public static function from(mixed $val, bool $strict = false): ?bool {
		$sub_res = false;
		if (Str::is($val))
			$val = Str::lower($val);
		if (!isset($val))
			return false;
		if (in_array($val, static::$array_yes))
			$sub_res = true;
		if ($strict) {
			if ($sub_res)
				return true;
			if (in_array($val, static::$array_no))
				return false;

			return null;
		}
		return $sub_res;
	}

	public static function to(mixed $val): string {
		return static::from($val)?static::$to_yes:static::$to_no;
	}
}
