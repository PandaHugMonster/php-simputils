<?php


namespace spaf\simputils\traits\helpers;


use DateTimeZone;
use spaf\simputils\models\DateTime;

/**
 * DateTime Helper Trait
 *
 * Provides all needed functionality to work with dates and times
 *
 * @package spaf\simputils\traits\helpers
 */
trait DateTimeTrait {

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
		$default_now_string = empty(static::$now_string)
			?'now'
			:static::$now_string;
		return static::normalize($default_now_string);
	}

	/**
	 * Normalization of date and time
	 *
	 * Will always return DateTime or null, You can provide any datetime type, like int or DateTime
	 * or string, it will create a DateTime object of it. It works transparently with DateTime objects,
	 * but returns a new object, rather than the reference to an old object.
	 * So always expect a new object from here.
	 *
	 * For the purpose of optimization, you can enforce "reference" instead of new DateTime object,
	 * but you have to make sure you understand all the risks, and strongly recommended to avoid
	 * using this param, when possible
	 *
	 * @param \DateTime|string|int $dt  You datetime data you want to normalize
	 * @param \DateTimeZone|null $tz    Your TimeZone if applicable
	 * @param string|null $fmt Allows to enforce datetime format, though it's usually not needed.
	 *                                  This parameter plays role only in case of the input datetime
	 *                                  in string type.
	 * @param bool $is_clone_allowed    If false and DateTime object supplied, the same object is returned,
	 *                                  instead of the cloned one (instead of a new object). Default is true.
	 *
	 * @return DateTime|null
	 * @throws \Exception
	 */
	public static function normalize(
		DateTime|string|int $dt,
		DateTimeZone|null $tz = null,
		string $fmt = null,
		bool $is_clone_allowed = true,
	): ?DateTime {

		if (is_string($dt)) {
			$res = !empty($fmt)
				?DateTime::createFromFormat($fmt, $dt, $tz)
				:new DateTime($dt, $tz);
		} elseif (is_integer($dt))
			$res = new DateTime(date(DATE_ATOM, $dt), $tz);
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

}