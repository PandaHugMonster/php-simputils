<?php


namespace spaf\simputils;


use spaf\simputils\models\DataUnit;
use function is_integer;

/**
 * Helps to operate with data-units conversion, etc.
 *
 * Helps with different data-types and conversion between different units.
 *
 * Please, use any of UPPER or lower case. You can even mix those cases if you want.
 * "kB", "Kb", "KB", "kb" - all mean the same in the context of the helper
 *
 * @package spaf\simputils\helpers
 */
class Data {

	/**
	 * Shortcut for creation of DataUnit object
	 *
	 * @param null|int|string|DataUnit $value  Data Unit in any format (string, int, null, object)
	 * @param string|null              $format Default user format for the object
	 *
	 * @return \spaf\simputils\models\DataUnit
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented Redefinable component is not defined
	 */
	static function du(
		null|int|string|DataUnit $value = null,
		?string $format = null
	): DataUnit {
		if ($value instanceof DataUnit) {
			return $value;
		}
		if (empty($value)) {
			$value = '0B';
		}
		if (is_integer($value)) {
			$value = $value.'B';
		}
		$class = PHP::redef(DataUnit::class);
		$obj = new $class($value);
		$obj->user_format = $format ?? $obj->user_format;
		return $obj;
	}
}
