<?php


namespace spaf\simputils;


use spaf\simputils\exceptions\NonExistingDataUnit;
use spaf\simputils\exceptions\UnspecifiedDataUnit;
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
 * @todo Bug with above YB level (fix it asap!)
 * @package spaf\simputils\helpers
 */
class Data {

	/**
	 * Represents the unit of "Byte"
	 */
	const DATA_UNIT_BYTE = 'B';

	/**
	 * Represents the unit of "Kilobyte"
	 */
	const DATA_UNIT_KILOBYTE = 'KB';

	/**
	 * Represents the unit of "Megabyte"
	 */
	const DATA_UNIT_MEGABYTE = 'MB';

	/**
	 * Represents the unit of "Gigabyte"
	 */
	const DATA_UNIT_GIGABYTE = 'GB';

	/**
	 * Represents the unit of "Terabyte"
	 */
	const DATA_UNIT_TERABYTE = 'TB';

	/**
	 * Represents the unit of "Petabyte"
	 */
	const DATA_UNIT_PETABYTE = 'PB';

	/**
	 * Represents the unit of "Exabyte"
	 */
	const DATA_UNIT_EXABYTE = 'EB';

	/**
	 * Represents the unit of "Zettabyte"
	 */
	const DATA_UNIT_ZETTABYTE = 'ZB';

	/**
	 * Represents the unit of "Yottabyte"
	 */
	const DATA_UNIT_YOTTABYTE = 'YB';

	/**
	 * Returns assoc-array of the powers
	 *
	 * Keys - are the abbreviation from the constants of the class
	 * Values - are the powers of tens of 2 (so value 3 means **pow(2, 30)** , value 7 means **pow(2, 70)** )
	 *
	 * **Important:** In case of further creation of the new units this array will be extended as soon as
	 * the fact and naming will become known to the author/community
	 *
	 * @return array Array with keys representing the abbreviations of units and the values representing powers
	 */
	protected static function unitCodeToPowerArray(): array {
		return [
			static::DATA_UNIT_BYTE => 0,
			static::DATA_UNIT_KILOBYTE => 1,
			static::DATA_UNIT_MEGABYTE => 2,
			static::DATA_UNIT_GIGABYTE => 3,
			static::DATA_UNIT_TERABYTE => 4,
			static::DATA_UNIT_PETABYTE => 5,
			static::DATA_UNIT_EXABYTE => 6,
			static::DATA_UNIT_ZETTABYTE => 7,
			static::DATA_UNIT_YOTTABYTE => 8,
		];
	}

	/**
	 * Clears unit abbreviation
	 *
	 * Does 2 things:
	 *  1. Changes to UPPERCASE and removes all the non-letter symbols from the incoming string
	 *  2. Checks if the abbreviation is permitted
	 *
	 * Generally you could provide in a normal human readable form: "2.5mb" it will pickup
	 * and return the "MB" from it.
	 *
	 * In case of wrong format or non-existing units - will raise exceptions
	 *
	 * ```php
	 * $res = static::clear_unit('34tb');
	 * echo "$res\n";
	 * // Output would be: 'TB'
	 *
	 * $res = static::clear_unit('GB');
	 * echo "$res\n";
	 * // Output would be: 'GB'
	 * ```
	 *
	 * @param string $unit Unit that should be cleared and normalized (can include digits,
	 *                     that will be removed)
	 *
	 * @return string Cleared and normalized unit abbreviation
	 * @throws NonExistingDataUnit
	 * @throws UnspecifiedDataUnit
	 * @see Data::unitCodeToPowerArray()
	 *
	 */
	public static function clearUnit(string $unit): string {
		$unit_codes = array_keys(static::unitCodeToPowerArray());
		$unit = preg_replace('/[^A-Z]/', '', Str::upper($unit));
		if (empty($unit))
			throw new UnspecifiedDataUnit();
		if (!in_array($unit, $unit_codes))
			throw new NonExistingDataUnit("Such data unit as \"{$unit}\" is not known");
		return $unit;
	}

	/**
	 * Conversion between data units (any to any)
	 *
	 * Convert data amount with units (b, kb, mb, ...) to specified one (b, kb, mb, ...)
	 * Both strings must contain data unit abbreviation.
	 * Besides that the first argument must contain number, all the non-letter in the second argument will be ignored.
	 *
	 * In the first argument the abbreviation must be AFTER the numerical value.
	 * The numerical value must be PHP-parsable.
	 *
	 * The bytes fraction is not permitted, any float value of bytes will be rounded up (math rounding up).
	 *
	 * ```php
	 * $res = DataHelper::unit_to('12mb', 'kb');
	 * echo "$res\n";
	 * // Output would be (kb): 12288
	 *
	 * $res = DataHelper::unit_to('10240kb', 'kb');
	 * echo "$res\n";
	 * // Output would be (kb): 10240
	 *
	 * $res = DataHelper::unit_to('530GB', 'TB');
	 * echo "$res\n";
	 * // Output would be (tb): 0.52
	 *
	 * $res = DataHelper::unit_to('5kb');
	 * echo "$res\n";
	 * // Output would be (b): 5120
	 * ```
	 *
	 * @param string $value Value from, must contain number and abbreviation. Example: "20.5mb"
	 * @param string $to_unit The optional unit abbreviation to which convert, by default is set to bytes
	 *
	 * @return float|int Resulting value of conversion
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public static function unitTo(string $value, string $to_unit = self::DATA_UNIT_BYTE): float|int {
		$unit_codes = static::unitCodeToPowerArray();
		$from_unit = static::clearUnit($value, $unit_codes);
		$to_unit = static::clearUnit($to_unit, $unit_codes);
		$from_power = $unit_codes[$from_unit];
		$to_power = $unit_codes[$to_unit];

		$value = floatval($value);
		if ($from_power != $to_power) {
			$diff = 2**(abs($from_power - $to_power) * 10);
			$value = $from_power > $to_power?($value * $diff):($value / $diff);
		}

		return round($value, $to_unit == static::DATA_UNIT_BYTE?0:2);
	}

	/**
	 * Alias for {@see unitTo()} but first argument integer will be considered as "bytes"
	 *
	 * ```php
	 * $res = DataHelper::bytes_to(10240, 'MB');
	 * echo "$res\n";
	 * // Output would be (mb): 0.01
	 *
	 * $res = DataHelper::bytes_to(1024, 'kb');
	 * echo "$res\n";
	 * // Output would be (kb): 1
	 * ```
	 *
	 * @param int $bytes Size in bytes that should be converted into specified in "$to_unit" level
	 * @param string $to_unit Represents abbreviation of unit-type into which the value of "$bytes"
	 * should be converted to
	 *
	 * @return float Resulting value of "$to_unit" level as a float number
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 * @see \spaf\simputils\helpers\Data::unitTo()
	 */
	public static function bytesTo(int $bytes, string $to_unit): float {
		return static::unitTo("{$bytes}b", $to_unit);
	}

	/**
	 * Any data unit value to bytes conversion
	 *
	 * ```php
	 * $res = DataHelper::to_bytes('10240ZB');
	 * echo "$res\n";
	 * // Output would be: 1.2089258196146E+25
	 *
	 * $res = DataHelper::to_bytes('500MB');
	 * echo "$res\n";
	 * // Output would be: 524288000
	 *
	 * $res = DataHelper::to_bytes('10000kb');
	 * echo "$res\n";
	 * // Output would be: 10240000
	 *
	 * $res = DataHelper::to_bytes('1050b');
	 * echo "$res\n";
	 * // Output would be: 1050
	 * ```
	 *
	 * @param string $value Value of "number and abbreviation" that should be converted into bytes
	 *
	 * @return int Amount of bytes converted from incoming value
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public static function toBytes(string $value): int {
		return intval(static::unitTo($value, static::DATA_UNIT_BYTE));
	}

	/**
	 * Round to the reasonable human-readable unit
	 *
	 * ```php
	 * use spaf\simputils\helpers\DataHelper;
	 * use function spaf\simputils\basic\pd;
	 *
	 * $res = DataHelper::human_readable(123456789);
	 * echo "$res\n";
	 * // Output would be: 117.74MB
	 *
	 * $res = DataHelper::human_readable('10240ZB');
	 * echo "$res\n";
	 * // Output would be: 10YB
	 *
	 * $res = DataHelper::human_readable('1023kb');
	 * echo "$res\n";
	 * // Output would be: 1023KB
	 *
	 * $res = DataHelper::human_readable('1025kb');
	 * echo "$res\n";
	 * // Output would be: 1MB
	 * ```
	 *
	 * In the last example, because extra 1 kb is not affecting first 2 digits after coma - it might
	 * not be part of the result (**This is why the result is 1MB**)
	 *
	 * @param int|string $value Integer value will be considered as "bytes", in case of string - the abbreviation will
	 * be used to determine the level
	 *
	 * @return string|null String with the resulting measure and correctly abbreviated level
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public static function humanReadable(int|string $value): ?string {
		$res = null;
		$value = is_numeric($value)?"{$value}b":$value;
		foreach (static::unitCodeToPowerArray() as $unit_code => $power) {
			$_temp_value = static::unitTo($value, $unit_code);
			if ($_temp_value < 1024) {
				$res = "{$_temp_value}{$unit_code}";
				break;
			}
		}
		return $res;
	}

	/**
	 * Shortcut for creation of DataUnit object
	 *
	 * @param null|int|string|DataUnit $value
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
