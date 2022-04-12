<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function mb_chr;
use function round;

// FIX  Implement string parsing functionality
// FIX  Improve architecture and code base formatting

/**
 *
 * @property-read float|int $fahrenheit
 * @property-read float|int $celsius
 * @property-read float|int $kelvin
 */
class Temperature extends SimpleObject {
	use RedefinableComponentTrait;
	use ForOutputsTrait;

	const UNIT_KELVIN = 'K';
	const UNIT_CELSIUS = 'C';
	const UNIT_FAHRENHEIT = 'F';

	const ABS_ZERO_POINT_KELVIN = 0;
	const ABS_ZERO_POINT_CELSIUS = -273.15;
	const ABS_ZERO_POINT_FAHRENHEIT = -459.67;

	public static $default_unit = self::UNIT_CELSIUS;
	public static $store_format = self::UNIT_CELSIUS;
	public static $disable_degree_symbol = false;
	public static int|false $auto_round = 2;

	protected float $_value = 0;
	protected ?string $_unit = null;

	/**
	 * FIX  Implement proper parsing, and no separate unit
	 *
	 * @param float $value
	 * @param string|null $unit
	 */
	public function __construct(float $value = 0, ?string $unit = null) {
		$this->_unit = $unit ?? static::$default_unit;
		$this->_value = static::absoluteZeroLimiter($value, $this->_unit);
	}

	public static function absoluteZeroLimiter(float $value, $unit = self::UNIT_CELSIUS) {
		$limiter = static::ABS_ZERO_POINT_KELVIN;
		switch ($unit) {
			case static::UNIT_FAHRENHEIT: $limiter = static::ABS_ZERO_POINT_FAHRENHEIT; break;
			case static::UNIT_CELSIUS: $limiter = static::ABS_ZERO_POINT_CELSIUS; break;
		}

		return $value >= $limiter
			?$value
			:$limiter;
	}

	public static function convert(
		float $value,
		string $from = self::UNIT_CELSIUS,
		string $to = self::UNIT_CELSIUS
	) {
		$value = static::absoluteZeroLimiter($value, $from);

		if ($from === $to) {
			return $value;
		}
		// TODO Could be significantly optimized
		$pre_res = match (true) {

			$from === static::UNIT_FAHRENHEIT && $to === static::UNIT_CELSIUS
				=> ($value - 32) * 5 / 9,

			$from === static::UNIT_CELSIUS && $to === static::UNIT_FAHRENHEIT
				=> ($value * 9 / 5) + 32,

			// NOTE Will cause +273.15
			$from === static::UNIT_CELSIUS && $to === static::UNIT_KELVIN
				=> $value - static::ABS_ZERO_POINT_CELSIUS,

			// NOTE Will cause -273.15
			$from === static::UNIT_KELVIN && $to === static::UNIT_CELSIUS
				=> $value + static::ABS_ZERO_POINT_CELSIUS,

			$from === static::UNIT_FAHRENHEIT && $to === static::UNIT_KELVIN
				=> (($value - 32) * 5 / 9) - static::ABS_ZERO_POINT_CELSIUS,

			$from === static::UNIT_KELVIN && $to === static::UNIT_FAHRENHEIT
				=> ((($value + static::ABS_ZERO_POINT_CELSIUS) * 9 / 5) + 32),
		};

		$res = static::absoluteZeroLimiter($pre_res, $to);
		if (static::$auto_round === false) {
			return $res;
		}
		return round($res, static::$auto_round);
	}

	public function symbol(string $unit = null, bool $no_unit = false) {
		$symbol = mb_chr(0x00B0, 'utf-8');
		$u = $unit ?? $this->_unit ?? static::$default_unit;
		if ($no_unit) {
			return "{$symbol}";
		}
		return "{$u}{$symbol}";
	}

	#[Property('fahrenheit')]
	protected function getFahrenheit(): float|int {
		return static::convert($this->_value, $this->_unit, static::UNIT_FAHRENHEIT);
	}

	#[Property('celsius')]
	protected function getCelsius(): float|int {
		return static::convert($this->_value, $this->_unit, static::UNIT_CELSIUS);
	}

	#[Property('kelvin')]
	protected function getKelvin(): float|int {
		return static::convert($this->_value, $this->_unit, static::UNIT_KELVIN);
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		// FIX  Store format is not implemented!
		return "{$this->_value} {$this->_unit}";
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		$symbol = !static::$disable_degree_symbol
			?" {$this->symbol()}"
			:'';

		if (static::$auto_round !== false) {
			$val = round($this->_value, static::$auto_round);
		}
		return "{$val}{$symbol}";
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	public static function redefComponentName(): string {
		return InitConfig::REDEF_TEMPERATURE;
	}
}
