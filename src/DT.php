<?php


namespace spaf\simputils;


use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\models\DateInterval;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\DateTimeZone;
use function date_default_timezone_get;
use function date_default_timezone_set;
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
	const FMT_DATETIME = self::FMT_DATE.' '.self::FMT_TIME;
	const FMT_DATETIME_FULL = self::FMT_DATETIME.'.u';
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
	 * @param DateTime|string|int           $dt               You datetime data you want
	 *                                                        to normalize
	 * @param bool|DateTimeZone|string|null $tz               Your TimeZone if applicable
	 * @param string|null                   $fmt              Allows to enforce datetime format,
	 *                                                        though it's usually not needed.
	 *                                                        This parameter plays role only
	 *                                                        in case of the input datetime in
	 *                                                        string type.
	 * @param bool                          $is_clone_allowed If false and DateTime object supplied,
	 *                                                        the same object is returned, instead
	 *                                                        of the cloned one (instead of
	 *                                                        a new object). Default is true.
	 *
	 * @return DateTime|null
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented Redefinable component is not defined
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public static function normalize(
		DateTime|string|int $dt,
		null|bool|DateTimeZone|string $tz = null,
		string $fmt = null,
		bool $is_clone_allowed = true,
	): ?DateTime {
		if ($dt instanceof DateTime) {
			return $is_clone_allowed
				?clone $dt
				:$dt;
		}

		$class = PHP::redef(DateTime::class);
		$tz_class = PHP::redef(DateTimeZone::class);

		$from_utc = false;

		// Incoming
		if ($tz === true) {
			// Zoned input
			$tz = null;
		} else if (is_string($tz)) {
			// Zoned input
			$tz = new $tz_class($tz);
		} else if (empty($tz)) {
			// Un-zoned input!
			$tz = new $tz_class('UTC');
			$from_utc = true;
		}
		/** @var DateTime $res */
		// Resulting
		if (Str::is($dt)) {
			$res = !empty($fmt)
				?$class::createFromFormat($fmt, $dt, $tz)
				:new $class($dt, $tz);
		} else if (is_integer($dt)) {
			$res = new $class(date(DATE_ATOM, $dt), $tz);
		}

		if ($from_utc) {
			$res->setTimezone(static::getDefaultTimeZone());
		}

		return $res;
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
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented Redefinable component is not defined
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

	public static function getDefaultTimeZone(): DateTimeZone {
		$class = PHP::redef(DateTimeZone::class);
		$tz = new $class(date_default_timezone_get());
		return $tz;
	}

	public static function setDefaultTimeZone(string|DateTimeZone $tz) {
		date_default_timezone_set("{$tz}");
	}
}
