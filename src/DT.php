<?php


namespace spaf\simputils;


use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\interfaces\helpers\DateTimeHelperInterface;
use spaf\simputils\models\DateInterval;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\DateTimeZone;
use function is_string;

/**
 * General purpose DateTime static helper
 *
 * Performs DateTime object generation, conversions, walk-through the dates and times.
 *
 * @see DateTime
 * @see \spaf\simputils\models\Date
 * @see \spaf\simputils\models\Time
 * @see DateInterval
 * @see \spaf\simputils\models\DatePeriod
 *
 */
class DT implements DateTimeHelperInterface {

	private static function _getClass() {
		return PHP::redef(DateTime::class);
	}

	public static ?string $now_string = null;

	/**
	 * Returns current datetime object
	 *
	 * Important note:  By defining static property $now_string you can specify any string
	 *                  instead of "now". This should not be used ever, but can help
	 *                  in some cases of testing/mocking and experimenting.
	 *
	 * @param \DateTimeZone|null $tz
	 *
	 * @return DateTime|null
	 * @throws \Exception
	 */
	public static function now(DateTimeZone|null $tz = null): ?DateTime {
		return static::normalize(static::$now_string ?? 'now');
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
	 * @param DateTime|string|int $dt   You datetime data you want to normalize
	 * @param \DateTimeZone|null $tz    Your TimeZone if applicable
	 * @param string|null $fmt          Allows to enforce datetime format, though it's usually not needed.
	 *                                  This parameter plays role only in case of the input datetime
	 *                                  in string type.
	 * @param bool $is_clone_allowed    If false and DateTime object supplied, the same object is
	 *                                  returned, instead of the cloned one (instead of a new object).
	 *                                  Default is true.
	 *
	 * @return DateTime|null
	 * @throws \Exception
	 */
	public static function normalize(
		DateTime|string|int $dt,
		null|DateTimeZone|string $tz = null,
		string $fmt = null,
		bool $is_clone_allowed = true,
	): ?DateTime {
		$class = static::_getClass();
		if (is_string($tz)) {
			$tz_class = PHP::redef(DateTimeZone::class);
			$tz = new $tz_class($tz);
		}

		if (Str::is($dt)) {
			$res = !empty($fmt)
				?$class::createFromFormat($fmt, $dt, $tz)
				:new $class($dt, $tz);
		} elseif (is_integer($dt))
			$res = new $class(date(DATE_ATOM, $dt), $tz);
		else
			$res = $is_clone_allowed?clone $dt:$dt;

		return $res;
	}

	/**
	 * Stringify date with normalization
	 *
	 * @param DateTime|string|int $dt
	 * @param string|null $fmt
	 * @param \DateTimeZone|null $tz
	 * @param string|null $parsing_fmt
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	public static function stringify(
		DateTime|string|int $dt,
		string $fmt = null,
		DateTimeZone|null $tz = null,
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
	 * @param \spaf\simputils\models\DateTime|string|int $start
	 * @param \spaf\simputils\models\DateTime|string|int $end
	 * @param string|\spaf\simputils\models\DateInterval $step
	 *
	 * @return \spaf\simputils\models\DatePeriod
	 * @throws \Exception
	 */
	#[Shortcut('\spaf\simputils\models\DateTime::walk()')]
	public static function walk(
		DateTime|string|int $start,
		DateTime|string|int $end,
		string|DateInterval $step
	) {
		return static::normalize($start)->walk(static::normalize($end), $step);
	}
}
