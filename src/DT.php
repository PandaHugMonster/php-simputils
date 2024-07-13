<?php


namespace spaf\simputils;


use Exception;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\exceptions\DateTimeParsingException;
use spaf\simputils\exceptions\RedefUnimplemented;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateInterval;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\DateTimeZone;
use spaf\simputils\models\TimeDuration;
use function date;
use function date_default_timezone_get;
use function date_default_timezone_set;
use function intval;
use function is_null;
use function is_numeric;
use function is_string;

/**
 * General purpose DateTime static helper
 *
 * Performs DateTime object generation, conversions, walk-through the dates and times.
 *
 * @see DateTime
 * @see \spaf\simputils\models\Date
 * @see \spaf\simputils\models\Time
 * @see \spaf\simputils\models\DateInterval
 * @see \spaf\simputils\models\DatePeriod
 *
 */
class DT {

	const FMT_DATE = 'Y-m-d';
	const FMT_TIME = 'H:i:s';

	const FMT_TIME_EXT = self::FMT_TIME;
	const FMT_TIME_FULL = self::FMT_TIME_EXT.'.u';

	const FMT_DATETIME = self::FMT_DATE.' '.self::FMT_TIME;
	const FMT_DATETIME_EXT = self::FMT_DATE.' '.self::FMT_TIME_EXT;
	const FMT_DATETIME_FULL = self::FMT_DATE.' '.self::FMT_TIME_FULL;

	const FMT_STRINGIFY_DEFAULT = self::FMT_DATETIME_FULL;

	public static ?string $now_string = null;

	/**
	 * Returns current datetime object
	 *
	 * Important note:  By defining static property $now_string you can specify any string
	 *                  instead of "now". This should not be used ever, but can help
	 *                  in some cases of testing/mocking and experimenting.
	 *
	 * @param DateTimeZone|bool|string|null $tz Time zone
	 *
	 * @return DateTime|null
	 */
	public static function now(DateTimeZone|bool|string|null $tz = null): ?DateTime {
		return static::normalize(static::$now_string ?? 'now', $tz);
	}

	/**
	 * Just a simplified shortcut for `DateTimeHelper::normalize`
	 *
	 * @param DateTime|string|int      $dt  Any date-time representation (DateTime object, string,
	 *                                      int)
	 * @param null|DateTimeZone|string $tz  TimeZone
	 * @param string|null              $fmt FROM Format, usually not needed, just if you are using
	 *                                      a special date-time format to parse
	 *
	 * @return DateTime|null
	 *
	 */
	#[Shortcut('DT::normalize()')]
	public static function ts(
		DateTime|string|int $dt,
		null|bool|DateTimeZone|string $tz = null,
		string $fmt = null
	): ?DateTime {
		return DT::normalize($dt, $tz, $fmt);
	}

	static function duration(int|float|DateInterval $value = 0): TimeDuration {
		$class = PHP::redef(TimeDuration::class);
		return new $class($value);
	}

	protected static function chooseTimeZoneForNormalization(
		null|bool|DateTimeZone|string $target_tz,
	) {
		$class = PHP::redef(DateTimeZone::class);

		$is_valid_str = is_string($target_tz) && Str::len($target_tz) > 0;

		if (is_string($target_tz) && !$is_valid_str) {
			throw new Exception('Empty string as timezone is not allowed.');
		}

		return match (true) {
			$is_valid_str
				=> [$o = new $class($target_tz), $o],
			$target_tz instanceof \DateTimeZone
				=> [$target_tz, $target_tz],
			$target_tz === false
				=> [$o = new $class('UTC'), $o],
			is_null($target_tz) || $target_tz === true
				=> [new $class('UTC'), static::getDefaultTimeZone()]
		};
	}

	private static $_try_field_formats = null;

	protected static function getTryFieldFormats(): Box {
		if (empty(static::$_try_field_formats)) {
			static::$_try_field_formats = PHP::box([
				"user_datetime_full_format",
				"user_datetime_ext_format",
				"user_datetime_format",

				"user_date_format",

				"user_time_full_format",
				"user_time_ext_format",
				"user_time_format",
			]);
		}

		return static::$_try_field_formats;
	}

	protected static function identifyFittingFormatAndDt(
		$settings_date_time,
		$tz_in,
		$dt,
		$fmt
	) {
		/** @var DateTime $class */
		$class = PHP::redef(DateTime::class);
		foreach (static::getTryFieldFormats() as $field) {
			try {
				$pre_fmt = $settings_date_time->get($field);
				if (!empty($pre_fmt)) {
					$pre_dt = $class::createFromFormat($pre_fmt, $dt, $tz_in);
					if ($pre_dt !== false) {
						$fmt = $pre_fmt;
						$dt = $pre_dt;
						break;
					}
				}
			} catch (Exception) {
				// NOTE Skipping, because we are simply trying out the cases to parse
			}
		}

		return [$dt, $fmt];
	}

	protected static function prepareDateTimeObjectBasedOnInput(
		string|int $dt,
		$tz_in,
		?string $fmt
	) {
		/** @var DateTime $class */
		$class = PHP::redef(DateTime::class);

		if (Str::is($dt)) {
			if (empty($fmt)) {
				$settings_date_time = PHP::box(PHP::ic()?->l10n?->settings_date_time);
				[$dt, $fmt] = static::identifyFittingFormatAndDt(
					$settings_date_time, $tz_in, $dt, $fmt,
				);
			} else {
				$dt = $class::createFromFormat($fmt, $dt, $tz_in);
				if (PHP::ic()->strict_mode && !$dt) {
					throw new DateTimeParsingException(
						"DateTime parsing failed. Format: \"{$fmt}\"; DT: \"{$dt}\""
					);
				}
			}

			if (empty($fmt) || $dt === false) {
				$dt = new $class($dt, $tz_in);
			}
		} else if (is_numeric($dt)) {
			$dt = new $class(date(DATE_ATOM, intval($dt)), $tz_in);
		}

		return $dt;
	}

	/**
	 * Normalization of date and time
	 *
	 * Will always return DateTime or null, You can provide any datetime type, like int or DateTime
	 * or string, it will create a DateTime object of it. It works transparently with
	 * DateTime objects, but returns a new object, rather than the reference to an old object.
	 * So always expect a new object from here.
	 *
	 * For the purpose of optimization, you can enforce "reference" instead of new DateTime object,
	 * but you have to make sure you understand all the risks, and strongly recommended to avoid
	 * using this param, when possible
	 *
	 * @param DateTime|string|int $dt You datetime data you want
	 *                                                        to normalize
	 * @param bool|DateTimeZone|string|null $tz Your TimeZone if applicable
	 * @param string|null $fmt Allows to enforce datetime format,
	 *                                                        though it's usually not needed.
	 *                                                        This parameter plays role only
	 *                                                        in case of the input datetime in
	 *                                                        string type.
	 * @param bool $is_clone_allowed If false and DateTime object supplied,
	 *                                                        the same object is returned, instead
	 *                                                        of the cloned one (instead of
	 *                                                        a new object). Default is true.
	 *
	 * @return DateTime|null
	 * @throws RedefUnimplemented Redefinable component is not defined
	 * @throws Exception Empty string as timezone is not allowed
	 */
	public static function normalize(
		DateTime|string|int $dt,
		null|bool|DateTimeZone|string $tz = null,
		?string $fmt = null,
		bool $is_clone_allowed = true,
	): ?DateTime {
		[$tz_in, $tz_out] = static::chooseTimeZoneForNormalization($tz);

		if ($dt instanceof DateTime) {
			$dt = $is_clone_allowed
				?clone $dt
				:$dt;
		} else {
			$dt = static::prepareDateTimeObjectBasedOnInput($dt, $tz_in, $fmt);
		}

		if ($dt) {
			$dt->tz = $tz_out;
		}

		return $dt;
	}

	protected static function composeIntervalSpecificationString(
		$obj,
		Box|array $cases,
	) {
		$res = '';
		foreach ($cases as $field => $spec) {
			if ($obj->$field) {
				$res .= "{$obj->$field}{$spec}";
			}
		}

		return $res;
	}

	static function dateIntervalSpecificationString(\DateInterval $obj) {
		$date = static::composeIntervalSpecificationString($obj, [
			'y' => 'Y',
			'm' => 'M',
			'd' => 'D',
		]);
		$time = static::composeIntervalSpecificationString($obj, [
			'h' => 'H',
			'i' => 'M',
			's' => 'S',
		]);

		if (!empty($time)) {
			$time = "T{$time}";
		}

		if (empty($date) && empty($time)) {
			return "PT0S";
		}

		if ($obj->invert) {
			return "-P{$date}{$time}";
		}

		return "P{$date}{$time}";
	}

	/**
	 * Stringify date with normalization
	 *
	 * @param DateTime|string|int      $dt          Date/Time reference (in any format string,
	 *                                              int, obj)
	 * @param string|null              $fmt         String output-format
	 * @param DateTimeZone|string|null $tz          Time zone reference
	 * @param string|null              $parsing_fmt Parsing string input-format hint
	 *
	 * @return string|null
	 * @throws RedefUnimplemented Redefinable component is not defined
	 */
	public static function stringify(
		DateTime|string|int $dt,
		string $fmt = null,
		DateTimeZone|string|null $tz = null,
		string $parsing_fmt = null,
	): ?string {

		$dt = static::normalize($dt, $tz, $parsing_fmt);
		return $dt->format(!empty($fmt)?$fmt:static::FMT_STRINGIFY_DEFAULT);
	}

	/**
	 * DatePeriod is returned for comfortable iterations
	 *
	 * Is somehow a shortcut to `DateTime::walk()` method
	 *
	 * Example 1:
	 * ```php
	 *  foreach (DT::walk('01.01.2022', '31.12.2022', '1 day') as $date) {
	 *      pr("$date");
	 *  }
	 * ```
	 * Will print out all the days of the year 2022
	 *
	 * Example 2:
	 * ```php
	 *  foreach (DT::walk('01.01.2022', '01.02.2022', '180 mins') as $date) {
	 *      pr("$date");
	 *  }
	 * ```
	 * Will print out all the hours between 2022-01-01 and 2022-02-01 with a step
	 * of 3 hours (180 mins)
	 *
	 * @param DateTime|string|int $start Start
	 * @param DateTime|string|int $end   End
	 * @param string|DateInterval $step  Step
	 *
	 * @return \spaf\simputils\models\DatePeriod
	 */
	#[Shortcut('\spaf\simputils\models\DateTime::walk()')]
	public static function walk(
		DateTime|string|int $start,
		DateTime|string|int $end,
		string|DateInterval $step
	) {
		return static::normalize($start)->walk(static::normalize($end), $step);
	}

	static function getListOfDaysOfWeek($is_iso = false) {
		// TODO Should be translated somehow
		if (!$is_iso) {
			return PHP::box([
				'Sunday',
				'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
				'Saturday'
			]);
		}

		return PHP::box([
			1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday',
			6 => 'Saturday', 7 => 'Sunday'
		]);
	}

	static function getListOfMonths() {
		// TODO Should be translated somehow
		return PHP::box([
			1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May',
			6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October',
			11 => 'November', 12 => 'December',
		]);
	}

	public static function getDefaultTimeZone(): DateTimeZone {
		$class = PHP::redef(DateTimeZone::class);
		$tz = new $class(date_default_timezone_get());
		return $tz;
	}

	public static function setDefaultTimeZone(string|DateTimeZone $tz) {
		date_default_timezone_set("{$tz}");
	}
}
