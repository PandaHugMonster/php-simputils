<?php


namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\generic\fixups\FixUpDateTime;

/**
 * DateTime model of the framework
 *
 * It's inherited from the php-native DateTime object
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
 * @property int $micro Microseconds at most 6 digits
 */
class DateTime extends FixUpDateTime {

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

	public function __toString(): string {
		return DT::stringify($this);
	}
}
