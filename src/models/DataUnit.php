<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\Data;
use spaf\simputils\exceptions\NonExistingDataUnit;
use spaf\simputils\exceptions\UnspecifiedDataUnit;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 *
 * FIX  Add functionality for fractions
 */
class DataUnit extends SimpleObject {
	use RedefinableComponentTrait;
	use ForOutputsTrait;

	const USER_FORMAT_HR = 'hr';

	/**
	 * Represents the unit of "Byte"
	 */
	const BYTE = 'B';

	/**
	 * Represents the unit of "Kilobyte"
	 */
	const KILOBYTE = 'KB';

	/**
	 * Represents the unit of "Megabyte"
	 */
	const MEGABYTE = 'MB';

	/**
	 * Represents the unit of "Gigabyte"
	 */
	const GIGABYTE = 'GB';

	/**
	 * Represents the unit of "Terabyte"
	 */
	const TERABYTE = 'TB';

	/**
	 * Represents the unit of "Petabyte"
	 */
	const PETABYTE = 'PB';

	/**
	 * Represents the unit of "Exabyte"
	 */
	const EXABYTE = 'EB';

	/**
	 * Represents the unit of "Zettabyte"
	 */
	const ZETTABYTE = 'ZB';

	/**
	 * Represents the unit of "Yottabyte"
	 */
	const YOTTABYTE = 'YB';


	public static $l10n_translations = null;
	public static $big_number_extension = null;

	#[DebugHide]
	protected BigNumber $_value;
	public string $user_format = self::USER_FORMAT_HR;

	public function __construct(string|int $value = 0) {
		$this->_value = static::toBytes($value);
		$this->_value->mutable = true;
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->_value;
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		return $this->format();
	}

	/**
	 * Outputs the value in the specified format
	 *
	 * By default uses "user format"
	 *
	 * @param ?string $format
	 * @param bool    $with_units
	 *
	 * @return string
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function format(?string $format = null, bool $with_units = true): string {
		$format = $format ?? $this->user_format;
		if ($format === DataUnit::USER_FORMAT_HR) {
			return static::humanReadable("{$this->_value}b");
		}
		return static::bytesTo($this->_value, $format).($with_units?Str::upper($format):null);
	}

	/**
	 * @param string $b Value to add With Unit
	 *
	 * @return $this
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function add(string $b): static {
		$this->_value->add(static::toBytes($b));
		return $this;
	}

	public function sub(string $b): static {
		$this->_value->sub(static::toBytes($b));
		return $this;
	}

	public function mul(BigNumber|int|string $b): static {
		$this->_value->mul($b);
		return $this;
	}

	public function div(BigNumber|int|string $b): static {
		$this->_value->div($b);
		return $this;
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
	public static function toBytes(string $value): BigNumber|string|int {
		return static::unitTo($value, DataUnit::BYTE);
	}

	/**
	 * Conversion between data units (any to any)
	 *
	 * Convert data amount with units (b, kb, mb, ...) to specified one (b, kb, mb, ...)
	 * Both strings must contain data unit abbreviation.
	 * Besides that the first argument must contain number, all the non-letter in
	 * the second argument will be ignored.
	 *
	 * In the first argument the abbreviation must be AFTER the numerical value.
	 * The numerical value must be PHP-parsable.
	 *
	 * The bytes fraction is not permitted, any float value of bytes will be rounded up
	 * (math rounding up).
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
	 * @param string $value   Value from, must contain number and abbreviation. Example: "20.5mb"
	 * @param string $to_unit The optional unit abbreviation to which convert, by default
	 *                        is set to bytes
	 *
	 * @return float|int Resulting value of conversion
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public static function unitTo(string $value, string $to_unit = DataUnit::BYTE): BigNumber {
		$unit_codes = Data::unitCodeToPowerArray();
		$from_unit = static::clearUnit($value);
		$to_unit = static::clearUnit($to_unit);
		$from_power = $unit_codes[$from_unit];
		$to_power = $unit_codes[$to_unit];

		$ext = static::$big_number_extension;
		$value = preg_replace('/[^0-9\-.]/', '', $value);
		$value = new BigNumber($value, extension: $ext);
		if ($from_power != $to_power) {
			$diff = (new BigNumber(2, false, extension: $ext))
				->pow(abs($from_power - $to_power) * 10);

			$value = $from_power > $to_power
				?$diff->mul($value)
				:$value->div($diff);
		}

		return $value;
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
	 *
	 * @see static::unitCodeToPowerArray()
	 */
	public static function clearUnit(string $unit): string {
		$unit_codes = Data::unitCodeToPowerArray();
		$unit = preg_replace('/[^A-Z]/', '', Str::upper($unit));
		if (empty($unit)) {
			throw new UnspecifiedDataUnit();
		}
		if (!$unit_codes->containsKey($unit)) {
			throw new NonExistingDataUnit("Such data unit as \"{$unit}\" is not known");
		}
		return $unit;
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
	 * @param int    $bytes   Size in bytes that should be converted into specified
	 *                        in "$to_unit" level
	 * @param string $to_unit Represents abbreviation of unit-type into which the value of "$bytes"
	 *                        should be converted to
	 *
	 * @return float Resulting value of "$to_unit" level as a float number
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 * @see \spaf\simputils\helpers\Data::unitTo()
	 */
	public static function bytesTo(string|int $bytes, string $to_unit): BigNumber {
		return static::unitTo("{$bytes}b", $to_unit);
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
	 * @param int|string $value Integer value will be considered as "bytes",
	 *                          in case of string - the abbreviation will be used to determine
	 *                          the level
	 *
	 * @return string|null String with the resulting measure and correctly abbreviated level
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public static function humanReadable(BigNumber|int|string $value): null|BigNumber|string {
		// FIX  Refactor
		$res = null;
		$value = is_numeric($value)?"{$value}b":$value;
		foreach (Data::unitCodeToPowerArray() as $unit_code => $power) {
			$res = static::unitTo($value, $unit_code);
			if ($res->cmp(1024) < 0) {
				break;
			}
		}
		return "{$res}{$unit_code}";
	}

	/**
	 * Translates data-units
	 *
	 * An argument must be in initial "english" format. This translator is uni-directional,
	 * From english abbreviation to another language abbreviation
	 *
	 * @param string $name English abbreviation (as constant values)
	 *
	 * @return false|mixed
	 */
	public static function translator(string $name) {
		$name = Str::upper($name);
		$check = new Box(static::$l10n_translations);
		if ($check->containsKey($name)) {
			$name = $check[$name];
		}
		return $name;
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATA_UNIT;
	}

	public function __toString(): string {
		return $this->for_user;
	}
}
