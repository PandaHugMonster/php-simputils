<?php
namespace spaf\simputils\models;

use DateTimeInterface;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\DT;
use spaf\simputils\generic\fixups\FixUpDateTime;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use function date_interval_create_from_date_string;
use function is_null;
use function is_string;
use function json_encode;
use function trim;

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
 * @property \spaf\simputils\models\DateTimeZone|string $tz Timezone change
 *
 * @property int $week ISO 8601 week number of year, weeks starting on Monday
 * @property-read int $doy The day of the year (starting from 0)
 *
 * @property int $year Year
 * @property int $month Month
 * @property int $day Day
 *
 * @property-read int $dow Numeric representation of the day of the week 0 (su) - 6 (sa)
 * @property-read int $dow_iso Numeric representation of the day of the week 1 (mo) - 7 (su)
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
 * @property-read bool $is_weekend Is day a weekend
 * @property-read bool $is_weekday Is day a week-day
 *
 * @property-read int $timestamp Amount of seconds since 1970-01-01 00:00:00 (can be negative)
 */
class DateTime extends FixUpDateTime {
	use ForOutputsTrait;

	public static $l10n_user_date_format = DT::FMT_DATE;
	public static $l10n_user_time_format = DT::FMT_TIME;
	public static $l10n_user_datetime_format = DT::FMT_DATETIME;

	public static $l10n_user_time_ext_format = DT::FMT_TIME_EXT;
	public static $l10n_user_time_full_format = DT::FMT_TIME_FULL;
	public static $l10n_user_datetime_ext_format = DT::FMT_DATETIME_EXT;
	public static $l10n_user_datetime_full_format = DT::FMT_DATETIME_FULL;

	// NOTE Is not used anywhere, just a reference for the JSON files
	public static $l10n_user_default_tz = 'UTC';

	public function __construct(
		DateTime|string $datetime = 'now',
		null|string|DateTimeZone $timezone = null
	) {
		$class_tz = PHP::redef(DateTimeZone::class);

		if ($datetime instanceof DateTime) {
			$_tmp = $datetime;
			$datetime = $_tmp->for_system;
			if (is_null($timezone)) {
				$timezone = $_tmp->tz;
			}
		}

		if (empty($timezone)) {
			$timezone = 'UTC';
		}

		if (is_string($timezone)) {
			$timezone = new $class_tz($timezone);
		}

		parent::__construct($datetime, $timezone);
	}

	/**
	 * Stores the copy of value before any of "modify", "add" or "sub" performed.
	 * @var static $_orig_value
	 */
	#[DebugHide]
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

	#[Property('dow_iso')]
	protected function getDowIso(): int {
		return (int) $this->format('N');
	}

	#[Property('is_weekend')]
	protected function getIsWeekend(): bool {
		return $this->dow_iso > 5;
	}

	#[Property('is_weekday')]
	protected function getIsWeekday(): bool {
		return !$this->is_weekend;
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

	/**
	 * @param \spaf\simputils\models\DateTimeZone|string $timezone Time zone
	 *
	 * @return static
	 */
	#[Property('tz', type: 'set')]
	#[\ReturnTypeWillChange]
	public function setTimezone($timezone): static {
		// IMP  This method is original native PHP method, and it expects to return something,
		//      And when it's used as a method it works exactly the same, but in case of
		//      property - it will be used ONLY for setting, without returning anything.
		//      This is why return-type signature has no definition!
		$class_tz = PHP::redef(DateTimeZone::class);

		if (empty($timezone)) {
			$timezone = 'UTC';
		}

		if (is_string($timezone)) {
			$timezone = new $class_tz($timezone);
		}
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

		/** @noinspection PhpUndefinedMethodInspection */
		return $class_date_i::expandFrom($res, new $class_date_i('P1D'));
	}

	public function walk(string|DateTime|int $to_date, string|DateInterval $step) {
		$class_date_p = PHP::redef(DatePeriod::class);
		$class_date_i = PHP::redef(DateInterval::class);

		/** @noinspection PhpUndefinedMethodInspection */
		$step = Str::is($step)
			?$class_date_i::createFromDateString($step)
			:$step;
		$to_date = DT::normalize($to_date);

		return new $class_date_p($this, $step, $to_date);
	}

	private function preparePeriodSideValue($val) {
		if (Str::is($val) &&
			(Str::startsWith($val, '+') || Str::startsWith($val, '-'))
		) {
			$val = trim($val);
			$nd = $this->clone();
			$nd->modify($val);
			return $nd;
		}

		return DT::normalize($val);
	}

	/**
	 * Period is a partial shortcut for "walk"
	 *
	 * If third argument is "false", then expect the exact shortcut of "walk".
	 * In the case when it's "true" (default one), it will make sure that the "start" is lower,
	 * than the "end" (in this case it can guarantee that the period goes from lower to
	 * higher date-time).
	 *
	 * @param string|static|int        $to_date        Second date
	 * @param string|DateInterval|null $step           Interval for iterations
	 * @param bool                     $is_direct_only If true (default) - start date
	 *                                                 will be always lower than the ending.
	 *
	 * @return DatePeriod
	 *
	 */
	#[Shortcut('walk')]
	public function period(
		string|DateTime|int $to_date,
		null|string|DateInterval $step = null,
		bool $is_direct_only = true
	) {
		$left = $this;
		$right = $this->preparePeriodSideValue($to_date);

		if ($is_direct_only && $left > $right) {
			$_mid = $left;
			$left = $right;
			$right = $_mid;
		}

		if (is_null($step)) {
			$step = $left->diff($right);
		}
		return $left->walk($right, $step);
	}

	/**
	 * Shortcut for "format"
	 *
	 * @param string $format Output format
	 *
	 * @return string
	 */
	#[Shortcut('static::format()')]
	function f($format) {
		return $this->format($format); //@codeCoverageIgnore
	}

	#[Property('timestamp')]
	#[Shortcut('static::getTimestamp()')]
	protected function getTimestampProperty(): int {
		return $this->getTimestamp();
	}

	public function format(string $format): string {
		$res = parent::format($format);
		return $res;
	}


	public function toJson(?bool $pretty = null, bool $with_class = false): string {
		// TODO Implement optional choice of "for_*"
		return json_encode($this->for_system);
	}

	public static function redefComponentName(): string {
		return AppInitConfig::REDEF_DATE_TIME;
	}

	function setFromData($data): static {
		$this->__construct();
		$tmp = DT::ts($data['for_system'], $data['tz']);
		$this->tz = $tmp->tz;
		$this->setTimestamp($tmp->getTimestamp());
		$this->setMicro($tmp->getMicro());
		$tmp = null;
		return $this;
	}

	function ___serialize(): Box|array {
		return [
			'for_system' => $this->for_system,
			'tz' => Str::ing($this->tz),
		];
	}

	protected function ___deserialize(array|Box $data): static {
		$this->setFromData($data);
		return $this;
	}
}
