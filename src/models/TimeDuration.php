<?php

namespace spaf\simputils\models;

use Exception;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\Math;
use spaf\simputils\PHP;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function floor;
use function intval;
use function is_finite;
use function is_int;
use function is_null;
use function trim;

/**
 *
 * @property-read float|int $value Value in seconds
 * @property ?string $max_converted_unit
 * @property ?string $min_displayed_unit
 * @property bool $is_negative
 *
 * @property-read int $millenniums
 * @property-read int $centuries
 * @property-read int $years
 * @property-read int $months
 * @property-read int $days
 * @property-read int $hours
 * @property-read int $minutes
 * @property-read int $seconds
 * @property-read int $milliseconds
 * @property-read int $microseconds
 *
 * @property bool $is_always_microseconds
 */
class TimeDuration extends SimpleObject {
	use RedefinableComponentTrait;
	use ForOutputsTrait;

	const MICRO_SECONDS = 'micro-seconds';
	const MILLI_SECONDS = 'milli-seconds';
	const SECONDS = 'seconds';
	const MINUTES = 'minutes';
	const HOURS = 'hours';
	const DAYS = 'days';
	const MONTHS = 'months';
	const YEARS = 'years';
	const CENTURIES = 'centuries';
	const MILLENNIUMS = 'millenniums';

	static $l10n_translations = null;

	static ?string $default_max_converted_unit = null;
	static ?string $default_min_displayed_unit = null;

	static float $month_coefficient = 30.436875;
	static float $day_coefficient = 24;
	static float $year_coefficient = 12;

	#[DebugHide]
	protected ?Box $cached = null;

	#[DebugHide]
	protected ?Box $cached_coefs = null;

//	#[DebugHide]
	protected ?Box $orig_coefs = null;

	#[Property]
	protected bool $_is_always_microseconds = false;

	#[DebugHide]
	protected int $_seconds = 0;
	#[DebugHide]
	protected float $_fractions = 0;

	#[Property]
	protected bool $_is_negative = false;

	#[DebugHide]
	protected ?string $_max_converted_unit = null;

	#[DebugHide]
	protected ?string $_min_displayed_unit = null;

	#[Property('max_converted_unit')]
	protected function getMaxConvertedUnit(): ?string {
		return $this->_max_converted_unit
			?? static::$default_max_converted_unit
			?? static::MILLENNIUMS;
	}

	#[Property('min_displayed_unit')]
	protected function getMinDisplayedUnit(): ?string {
		return $this->_min_displayed_unit
			?? static::$default_min_displayed_unit
			?? static::MICRO_SECONDS;
	}

	#[Property('max_converted_unit')]
	protected function setMaxConvertedUnit(?string $val) {
		$this->_max_converted_unit = $val;
		$this->_cachedValue(true);
	}

	#[Property('min_displayed_unit')]
	protected function setMinDisplayedUnit(?string $val) {
		$this->_min_displayed_unit = $val;
		$this->_cachedValue(true);
	}

	#[Property('value')]
	protected function getValue(): float|int {
		$res = $this->_seconds + $this->_fractions;
		if ($this->_is_negative) {
			$res *= -1;
		}

		return $res;
	}

	function __construct(
		int|float|DateInterval $value = 0,
		?string $max_converted_unit = null,
		?string $min_displayed_unit = null
	) {

		$this->_max_converted_unit = $max_converted_unit;
		$this->_min_displayed_unit = $min_displayed_unit;

		if ($value instanceof DateInterval) {
			// FIX  Implement it!
		} else {
			if (is_finite($value)) {
				if (is_int($value)) {
					$this->_seconds = Math::abs($value);
				} else {
					$this->_is_negative = $value < 0;
					$this->_seconds = Math::abs(floor($value));
					$this->_fractions = Math::abs($value) - $this->_seconds;
				}
			} else {
				throw new Exception('Infinite values for time-duration are not supported');
			}
		}

		$this->_cachedValue(true);
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->value;
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		return $this->humanReadable();
	}

	protected function _cachedValue(bool $recache = false) {
		if (is_null($this->cached) || $recache) {
			$this->cached = $this->_calc();
		}
		return $this->cached;
	}

	function humanReadable(): ?string {
		$res = '';

		if ($this->_is_negative) {
			$res .= '- ';
		}

		//		$gg = PHP::box();
		//		$orig_coefs = PHP::box();
		//		$was_met_max = false;
		//		$use = PHP::box();
		//		foreach ($t as $i => [$name, $v]) {
		//			if ($name === $this->max_converted_unit) {
		//				$was_met_max = true;
		//			}
		//			if (!$was_met_max) {
		//				continue;
		//			}
		//			$use[] = $name;
		////			$gg[$i] = [$name, $v];
		//			if ($name === $this->min_displayed_unit) {
		//				break;
		//			}
		//		}
		//
		$params = PHP::box([
			PHP::box([static::MILLENNIUMS, 'mil.']),
			PHP::box([static::CENTURIES, 'c.']),
			PHP::box([static::YEARS, 'y.']),
			PHP::box([static::MONTHS, 'mon.']),
			PHP::box([static::DAYS, 'd.']),
			PHP::box([static::HOURS, 'h.']),
			PHP::box([static::MINUTES, 'min.']),
			PHP::box([static::SECONDS, 'sec.']),
		]);
		$c = $this->_cachedValue();

		$was_met_max = false;
		foreach ($params as $i => [$name, $label]) {
			if ($name === $this->max_converted_unit) {
				$was_met_max = true;
			}
			if (!$was_met_max) {
				continue;
			}
			if (($a = Math::abs($c[$name])) > 0) {
				$res .= "{$a}{$label} ";
			}
			if ($name === $this->min_displayed_unit) {
				break;
			}
		}

		$fractured_units = PHP::box([static::MICRO_SECONDS, static::MILLI_SECONDS]);
		if ($fractured_units->containsValue($this->min_displayed_unit) && ($a = Math::abs($this->microseconds)) > 0) {
			if (static::MICRO_SECONDS === $this->min_displayed_unit && ($this->is_always_microseconds || ($a % 1000) != 0)) {
				$unit = 'ms.';
				if ($a > 0) {
					$res .= "{$a}{$unit} ";
				}
			} else {
				$unit = 'mls.';
				$m = floor($a / 1000);
				if ($m > 0) {
					$res .= "{$m}{$unit} ";
				}
			}
		}

		$res = trim($res);
		return $res ?: null;
	}

	function numeric(?string $unit = null) {
		if (is_null($unit)) {
			$unit = $this->maxName();
		}
		if (!$this->cached_coefs->containsKey($unit)) {
			throw new Exception("Unsupported unit {$unit}");
		}
		$coef = $this->cached_coefs[$unit];
		return $this->value / $coef;
	}

	function maxName() {
		$unit = null;
		$c = $this->_cachedValue();
		foreach ($c as $name => $value) {
			if ($value > 0) {
				$unit = $name;
				break;
			}
		}
		return $unit;
	}

	protected function _calc() {
		$coefs = PHP::box();
		$res = PHP::box();
		$pre_coefs = PHP::box([
			PHP::box(['millenniums', 1000]),
			PHP::box(['centuries', 100]),
			PHP::box(['years', static::$year_coefficient]),
			PHP::box(['months', static::$month_coefficient]),
			PHP::box(['days', static::$day_coefficient]),
			PHP::box(['hours', 60]),
			PHP::box(['minutes', 60]),
			PHP::box(['seconds', 1]),
		]);
		foreach ($pre_coefs as $i => [$name, $v1]) {
			$sub = $v1;
			foreach ($pre_coefs as $k => [$_, $v2]) {
				if ($k > $i) {
					$sub *= $v2;
				}
			}
			$coefs[$name] = (int) $sub;
		}

		$this->cached_coefs = $coefs;

		$remainder = Math::abs($this->value);
		foreach ($coefs as $name => $coef) {
			$res[$name] = intval($remainder / $coef);
			$remainder = intval($remainder) % $coef;
		}

		return $res;
	}

	#[Property('millenniums')]
	protected function getMillenniums(): int {
		return $this->_cachedValue()->get(static::MILLENNIUMS, 0);
	}

	#[Property('centuries')]
	protected function getCenturies(): int {
		return $this->_cachedValue()->get(static::CENTURIES, 0);
	}

	#[Property('years')]
	protected function getYears(): int {
		return $this->_cachedValue()->get(static::YEARS, 0);
	}

	#[Property('months')]
	protected function getMonths(): int {
		return $this->_cachedValue()->get(static::MONTHS, 0);
	}

	#[Property('days')]
	protected function getDays(): int {
		return $this->_cachedValue()->get(static::DAYS, 0);
	}

	#[Property('hours')]
	protected function getHours(): int {
		return $this->_cachedValue()->get(static::HOURS, 0);
	}

	#[Property('minutes')]
	protected function getMinutes(): int {
		return $this->_cachedValue()->get(static::MINUTES, 0);
	}

	#[Property('seconds')]
	protected function getSeconds(): int {
		return $this->_cachedValue()->get(static::SECONDS, 0);
	}

	#[Property('microseconds')]
	protected function getMicroseconds(): int {
		return Math::round($this->_fractions, 6) * 1000000;
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_TIME_DURATION;
	}
}
