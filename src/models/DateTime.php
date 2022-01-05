<?php


namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\traits\PropertiesTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 * DateTime model of the framework
 *
 * It's inherited from the php-native DateTime object
 *
 * TODO Add more reasonable fields like year and month, etc.
 *
 * @property-read string $date Date part
 * @property-read string $time Time part
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
 * @property-read int $micro Microseconds at most 6 digits
 */
class DateTime extends \DateTime {
	use PropertiesTrait;
	use RedefinableComponentTrait;

	#[Property('date')]
	protected function getDateExt(): string {
		return $this->format(DT::FMT_DATE);
	}

	#[Property('time')]
	protected function getTime(): string {
		return $this->format(DT::FMT_TIME);
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
	public function setTimezone($timezone): void {
		parent::setTimezone($timezone);
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

	#[Property('second')]
	protected function getSecond(): int {
		return (int) $this->format('s');
	}

	#[Property('micro')]
	protected function getMicro(): int {
		return (int) $this->format('u');
	}

	#[Property('milli')]
	protected function getMilli(): int {
		return (int) $this->format('v');
	}

	public function __toString(): string {
		return DT::stringify($this);
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_TIME;
	}
}
