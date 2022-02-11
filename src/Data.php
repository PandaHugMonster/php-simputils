<?php


namespace spaf\simputils;


use spaf\simputils\models\Box;
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
	 * Returns assoc-array of the powers
	 *
	 * Keys - are the abbreviation from the constants of the class
	 * Values - are the powers of tens of 2 (so value 3 means **pow(2, 30)** ,
	 * value 7 means **pow(2, 70)** )
	 *
	 * @return array|Box Array with keys representing the abbreviations of units and the
	 *                   values representing powers
	 * @throws \Exception
	 */
	public static function unitCodeToPowerArray(): array|Box {
		$class = PHP::redef(Box::class);
		return new $class([
			DataUnit::BYTE => 0,
			DataUnit::KILOBYTE => 1,
			DataUnit::MEGABYTE => 2,
			DataUnit::GIGABYTE => 3,
			DataUnit::TERABYTE => 4,
			DataUnit::PETABYTE => 5,
			DataUnit::EXABYTE => 6,
			DataUnit::ZETTABYTE => 7,
			DataUnit::YOTTABYTE => 8,
		]);
	}

	/**
	 * Shortcut for creation of DataUnit object
	 *
	 * @param null|int|string|DataUnit $value
	 * @param string|null $format
	 *
	 * @return \spaf\simputils\models\DataUnit
	 * @throws \Exception
	 */
	public static function du(
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
