<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\exceptions\NonExistingDataUnit;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function is_numeric;
use function json_encode;

/**
 *
 * @property-read bool   $fractions_supported  Whether fractions (float) supported
 *                                             by the big number extension
 * @property-read string $big_number_extension
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

	/**
	 * @var string $output_separator is a separator in between units symbols and digits
	 */
	public static $output_separator = ' ';

	/**
	 * @var bool $long_format is applicable only for `humanReadable()` method (and any user output)
	 */
	public static bool $long_format = false;

	#[DebugHide]
	protected BigNumber $_value;
	public string $user_format = self::USER_FORMAT_HR;

	#[Property('big_number_extension')]
	protected function getBigNumberExtension(): string {
		return $this->_value->extension;
	}

	#[Property('fractions_supported')]
	protected function getFractionsSupported(): bool {
		return $this->_value->fractions_supported;
	}

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
	 * Returns assoc-array of the powers
	 *
	 * Keys - are the abbreviation from the constants of the class
	 * Values - are the powers of tens of 2 (so value 3 means **pow(2, 30)** ,
	 * value 7 means **pow(2, 70)** )
	 *
	 * @return array|Box Array with keys representing the abbreviations of units and the
	 *                   values representing powers
	 */
	protected static function unitToPowerMap(): array|Box {
//		$class = PHP::redef(Box::class);
		return PHP::box([
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
	 * Outputs the value in the specified format
	 *
	 * By default uses "user format"
	 *
	 * @param ?string $format     Format
	 * @param bool    $with_units Output should include units
	 *
	 * @return string
	 *
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit Unit was not specified or missing
	 */
	public function format(?string $format = null, bool $with_units = true): string {
		$format = $format ?? $this->user_format;

		if ($format === DataUnit::USER_FORMAT_HR) {
			return static::humanReadable("{$this->_value}b");
		}

		return static::formattedStr(
			static::bytesTo($this->_value, $format),
			$with_units?Str::upper($format):null
		);
	}

	protected static function formattedStr($val, ?string $unit_code = null) {
		return $val.static::$output_separator.static::translator($unit_code, true);
	}

	/**
	 * @param string $b Value with unit that should be added to the object
	 *
	 * @return $this
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit Unit was not specified or missing
	 */
	public function add(string $b): self {
		$this->_value->add(static::toBytes($b));
		return $this;
	}

	/**
	 * @param string $b Value with unit that should be subtracted from the object
	 *
	 * @return $this
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit Unit was not specified or missing
	 */
	public function sub(string $b): self {
		$this->_value->sub(static::toBytes($b));
		return $this;
	}

	/**
	 * @param BigNumber|int|string $b Numeric value to use as a multiplier
	 *
	 * @return $this
	 */
	public function mul(BigNumber|int|string $b): self {
		$this->_value->mul($b);
		return $this;
	}

	/**
	 * @param BigNumber|int|string $b Numeric value to use as a divider
	 *
	 * @return $this
	 */
	public function div(BigNumber|int|string $b): self {
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
	 * @return BigNumber|string|int Amount of bytes converted from incoming value
	 *
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit Unit was not specified or missing
	 */
	protected static function toBytes(string $value): BigNumber|string|int {
		return static::unitTo($value);
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
	 * @return BigNumber Resulting value of conversion
	 *
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented  Redefinable component is not defined
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit Unit was not specified or missing
	 */
	protected static function unitTo(string $value, string $to_unit = DataUnit::BYTE): BigNumber {
		$unit_codes = static::unitToPowerMap();
		$from_unit = static::clearUnit($value);
		$to_unit = static::clearUnit($to_unit);
		$from_power = $unit_codes[$from_unit];
		$to_power = $unit_codes[$to_unit];

		$ext = static::$big_number_extension ?? BigNumber::$default_extension;

		// TODO Should be improved part with in_array()
		$class_bn = PHP::redef(BigNumber::class);
		$value = new $class_bn(static::clearNumber($value), extension: $ext);
		if ($from_power != $to_power) {
			$diff = (new $class_bn(2, false, extension: $ext))
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
	 *
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 *
	 * @see static::unitCodeToPowerArray()
	 */
	public static function clearUnit(string $unit): string {
		$unit_codes = static::unitToPowerMap();

		$unit = preg_replace('/[\W]/ui', '', Str::upper($unit));
		$unit = preg_replace('/[\d]/', '', $unit);

		if (empty($unit)) {
			$unit = 'B'; // @codeCoverageIgnore
			// throw new UnspecifiedDataUnit();
		}

		// NOTE Transparently translated if applicable
		$unit = static::translator($unit);

		if (!$unit_codes->containsKey($unit)) {
			throw new NonExistingDataUnit("Such data unit as \"{$unit}\" is not known");
		}
		return $unit;
	}

	public static function clearNumber(string|int $value) {
		preg_replace('/[^0-9\-.]/', '', $value);
		$is_negative = $value && $value[0] === '-';
		$value = preg_replace('/[^0-9.]/', '', $value);
		return $is_negative?"-{$value}":$value;
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
	 * @param string|int $bytes   Size in bytes that should be converted into specified
	 *                            in "$to_unit" level
	 * @param string     $to_unit Represents abbreviation of unit-type into which
	 *                            the value of "$bytes" should be converted to
	 *
	 * @return BigNumber Resulting value of "$to_unit" level as a float number
	 *
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented  Redefinable component is not defined
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit Unit was not specified or missing
	 * @see \spaf\simputils\Data::unitTo()
	 */
	protected static function bytesTo(string|int $bytes, string $to_unit): BigNumber {
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
	 * @param BigNumber|int|string $value Integer value will be considered as "bytes",
	 *                                    in case of string - the abbreviation will be used
	 *                                    to determine the level
	 *
	 * @return BigNumber|string|null String with the resulting measure and
	 *                               correctly abbreviated level
	 *
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit Unit does not exist, not recognized
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented  Redefinable component is not defined
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit Unit was not specified or missing
	 */
	public static function humanReadable(BigNumber|int|string $value): null|BigNumber|string {
		$res = '';
		$dctp = static::unitToPowerMap();
		foreach ($dctp->keys as $unit_code) {
			// NOTE Careful, do not optimize this without correct solution. Will cause issues.

			$y = is_numeric($value) || $value instanceof BigNumber;
			$accu = static::unitTo($y?"{$value}b":$value, $unit_code);
			if ($accu->cmp(1024) < 0) {
				if (static::$long_format) {
					return static::formattedStrLong($value, $accu, $unit_code);
				} else {
					return static::formattedStr($accu, $unit_code);
				}
			}
		}

		if (static::$long_format) {
			return static::formattedStrLong($value, $accu, $unit_code);
		}

		return static::formattedStr($accu, $unit_code);
	}

	protected static function formattedStrLong($val, $accu, ?string $right_limit): string {
		$res = '';
		$total_bytes = static::toBytes($val);
		$total_bytes->setMutable(false);

		$right_limit = static::clearUnit($right_limit);

		$prev_unit = null;
		foreach (static::unitToPowerMap() as $unit => $power) {
			$t = (new BigNumber(1024))->pow($power);
			$unit = static::clearUnit($unit);

			$left = $total_bytes->div($t)->floor();
			$right = $total_bytes->mod($t);
			$is_zero = $right->isZero();

			$right = static::formattedStr($right, DataUnit::BYTE);
			if ($prev_unit) {
				$right = static::unitTo($right, $prev_unit)->floor();
				$is_zero = $right->isZero();
				$right = static::formattedStr($right, $prev_unit);
			}
			if (!$is_zero) {
				$res = $right.($res?' ':'').$res;
			}

			if ($unit === $right_limit) {
				$res = static::formattedStr($left, $unit).($res?' ':'').$res;
				break;
			}

			$prev_unit = $unit;
		}

		return $res;
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
	protected static function translator(string $name, bool $reversed = false) {
//		$class_box = PHP::redef(Box::class);

		$name = Str::upper($name);
		$check = PHP::box(static::$l10n_translations ?? []);
		if (!$reversed) {
			if ($check->containsValue($name)) {
				return $check->getKeyByValue($name);
			}
		} else {
			if ($check->containsKey($name)) {
				return $check[$name];
			}
		}
		return $name;
	}

	/**
	 * Json formatted
	 *
	 * The result is numeric without the unit, though it's always in bytes.
	 *
	 * @inheritdoc
	 * @return string Numeric value in bytes
	 */
	public function toJson(?bool $pretty = null, bool $with_class = false): string {
		return json_encode($this->for_system);
	}

	public static function fromJson(string $json): static {
		$res = json_decode($json, true);
		if (is_numeric($res)) {
			$res = "{$res}b";
		}
		return new static($res);
	}

	function setFromData($data): static {
		$this->__construct($data['value']);
		return $this;
	}

	function ___serialize(): Box|array {
		return [
			'value' => $this->_value,
		];
	}

	protected function ___deserialize(array|Box $data): static {
		return $this->setFromData($data);
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public static function redefComponentName(): string {
		return AppInitConfig::REDEF_DATA_UNIT;
	}
}
