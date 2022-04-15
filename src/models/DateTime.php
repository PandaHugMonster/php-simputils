<?php


namespace spaf\simputils\models;

use DateTimeInterface;
use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\generic\fixups\FixUpDateTime;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use function date_interval_create_from_date_string;
use function is_null;
use function json_encode;

/**
 * DateTime model of the framework
 *
 * It's inherited from the php-native DateTime object
 *
 * When you want to operate or store the object as a string in absolute, UTC and proper format
 * use property `for_system`
 * ```php
 *  ts()
 *
 * ```
 *
 * @property-read \spaf\simputils\models\Date|string $date Date part (stringifiable object)
 *
 * @property-read \spaf\simputils\models\Time|string $time Time part
 * @property-read \spaf\simputils\models\DateTimeZone $tz
 *
 * @property int $week ISO 8601 week number of year, weeks starting on Monday
 * @property-read int $doy The day of the year (starting from 0)
 *
 * @property int $year Year
 * @property int $month Month
 * @property int $day Day
 *
 * @property-read int $dow Numeric representation of the day of the week
 *
 * @property int $hour Hours
 * @property int $minute Minutes
 * @property int $second Seconds
 *
 * @property-read int $milli Milliseconds, at most 3 digits
 * @property int $micro Microseconds at most 6 digits
 *
 * @property string $for_system Returns UTC absolute and ready to store in string format value
 * @property string $for_user Returns formatted relative to a user settings/locale
 *
 * @property-read DateTime $orig_value Original value, if any modifications were performed,
 *                                     or manually snapshot
 */
class DateTime extends FixUpDateTime {
	use ForOutputsTrait;

	public static $l10n_user_date_format = DT::FMT_DATE;
	public static $l10n_user_time_format = DT::FMT_TIME;
	public static $l10n_user_datetime_format = DT::FMT_DATETIME;

	// NOTE Is not used anywhere, just a reference for the JSON files
	public static $l10n_user_default_tz = 'UTC';

	/**
	 * Stores the copy of value before any of "modify", "add" or "sub" performed.
	 * @var static $_orig_value
	 */
	protected $_orig_value;

	#[Property('orig_value')]
	protected function getOrigValue(): static|null {
		return $this->_orig_value;
	}

	public function snapshotOrigValue(bool $overwrite = true) {
		if (empty($this->_orig_value) || $overwrite) {
			$this->_orig_value = clone $this;
		}
	}

	/**
	 *
	 * TODO Implement caching of the value
	 * @return \spaf\simputils\models\Date|string
	 */
	#[Property('date')]
	protected function getDateExt(): Date|string {
		return new Date($this);
	}

	/**
	 *
	 * TODO Implement caching of the value
	 * @return \spaf\simputils\models\Time|string
	 */
	#[Property('time')]
	protected function getTime(): Time|string {
		return new Time($this);
	}

	#[Property('week')]
	protected function getWeek(): int {
		return (int) $this->format('W');
	}

	#[Property('dow')]
	protected function getDow(): int {
		return (int) $this->format('w');
	}

	#[Property('doy')]
	protected function getDoy(): int {
		return (int) $this->format('z');
	}

	#[Property('tz')]
	public function getTimezone(): DateTimeZone|false {
		// Todo maybe implement caching
		return new DateTimeZone(parent::getTimezone()->getName());
	}

	#[Property('tz')]
	#[\ReturnTypeWillChange]
	public function setTimezone($timezone) {
		// IMP  This method is original native PHP method, and it expects to return something,
		//      And when it's used as a method it works exactly the same, but in case of
		//      property - it will be used ONLY for setting, without returning anything.
		//      This is why return-type signature has no definition!
		return parent::setTimezone($timezone);
	}

	#[Property('year')]
	protected function getYear(): int {
		return (int) $this->format('Y');
	}

	#[Property('year')]
	protected function setYear(int $year): void {
		$this->setDate($year, $this->month, $this->day);
	}

	#[Property('month')]
	protected function getMonth(): int {
		return (int) $this->format('n');
	}

	#[Property('month')]
	protected function setMonth(int $month): void {
		$this->setDate($this->year, $month, $this->day);
	}

	#[Property('day')]
	protected function getDay(): int {
		return (int) $this->format('j');
	}

	#[Property('day')]
	protected function setDay(int $day): void {
		$this->setDate($this->year, $this->month, $day);
	}

	#[Property('hour')]
	protected function getHour(): int {
		return (int) $this->format('G');
	}

	#[Property('hour')]
	protected function setHour(int $hour): void {
		$this->setTime($hour, $this->minute, $this->second, $this->micro);
	}

	#[Property('minute')]
	protected function getMinute(): int {
		return (int) $this->format('i');
	}

	#[Property('minute')]
	protected function setMinute(int $minute): void {
		$this->setTime($this->hour, $minute, $this->second, $this->micro);
	}

	#[Property('second')]
	protected function getSecond(): int {
		return (int) $this->format('s');
	}

	#[Property('second')]
	protected function setSecond(int $second): void {
		$this->setTime($this->hour, $this->minute, $second, $this->micro);
	}

	#[Property('micro')]
	protected function getMicro(): int {
		return (int) $this->format('u');
	}

	#[Property('micro')]
	protected function setMicro(int $micro): void {
		$this->setTime($this->hour, $this->minute, $this->second, $micro);
	}

	#[Property('milli')]
	protected function getMilli(): int {
		return (int) $this->format('v');
	}

	public function getForSystemObj() {
		$tz_class = PHP::redef(DateTimeZone::class);
		$obj = DT::normalize(
			$this,
			$this->tz,
			is_clone_allowed: true
		);
		$obj->setTimezone(new $tz_class('UTC'));
		return $obj;
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->getForSystemObj()->format(DT::FMT_DATETIME_FULL);
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		return $this->format(static::$l10n_user_datetime_format);
	}

	public function add(DateInterval|string|\DateInterval $interval): static {
		$this->snapshotOrigValue(false);

		if (Str::is($interval)) {
			$interval = date_interval_create_from_date_string($interval);
		}
		parent::add($interval);
		return $this;
	}

	public function sub(DateInterval|string|\DateInterval $interval): static {
		$this->snapshotOrigValue(false);

		if (Str::is($interval)) {
			$interval = date_interval_create_from_date_string($interval);
		}
		parent::sub($interval);
		return $this;
	}

	#[\ReturnTypeWillChange]
	public function modify(string $modifier) {
		$this->snapshotOrigValue(false);
		parent::modify($modifier); // TODO: Change the autogenerated stub
	}

	#[\ReturnTypeWillChange]
	public function diff(
		DateTimeInterface|DateTime|string|int|null $targetObject = null,
		bool $absolute = false
	) {
		$class_date_i = PHP::redef(DateInterval::class);
		if (is_null($targetObject)) {
			$targetObject = $this->_orig_value ?? clone $this;
		}
		$res = parent::diff(DT::normalize($targetObject), $absolute);
		return DateInterval::expandFrom($res, new $class_date_i('P1D'));
	}

	public function walk(string|DateTime|int $to_date, string|DateInterval $step) {
		$class_date_p = PHP::redef(DatePeriod::class);
		$step = Str::is($step)
			?DateInterval::createFromDateString($step)
			:$step;
		$to_date = DT::normalize($to_date);

		return new $class_date_p($this, $step, $to_date);
	}

	public function toJson(?bool $pretty = null, bool $with_class = false): string {
//		return json_encode($this->for_user);
		// TODO Implement optional choice of "for_*"
		return json_encode($this->for_system);
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_TIME;
	}
}
