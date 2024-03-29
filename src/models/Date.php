<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\fixups\FixUpDateTimePrism;
use spaf\simputils\Str;

/**
 * Date Prism
 *
 * It holds the reference to the original object of DateTime inside, and extracts Date relevant
 * data.
 *
 * @property-read string $for_system
 * @property-read string $for_user
 *
 * @property int         $week       ISO 8601 week number of year, weeks starting on Monday
 * @property-read int    $doy        The day of the year (starting from 0)
 *
 * @property int         $year       Year
 * @property int         $month      Month
 * @property int         $day        Day
 *
 * @property-read int    $dow        Numeric representation of the day of the week 0 (su) - 6 (sa)
 * @property-read int    $dow_iso    Numeric representation of the day of the week
 *
 * @property-read bool   $is_weekend Is day a weekend
 * @property-read bool   $is_weekday Is day a week-day
 */
class Date extends FixUpDateTimePrism {

	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->_object->getForSystemObj()->for_system;
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		$obj = $this->_object;

		return $obj->format(DateTime::$l10n_user_date_format);
	}

	/**
	 * @codeCoverageIgnore
	 * @return Box|array
	 */
	function ___serialize(): Box|array {
		return [
			'for_system' => $this->for_system,
			'tz' => Str::ing($this->tz),
		];
	}

	/**
	 * @param array|Box $data
	 *
	 * @codeCoverageIgnore
	 * @return $this
	 */
	protected function ___deserialize(array|Box $data): static {
		return $this->setFromData($data);
	}

	/**
	 * @param $data
	 *
	 * @codeCoverageIgnore
	 * @return $this
	 */
	function setFromData($data): static {
		$this->__construct($data['for_system'], $data['tz']);

		return $this;
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('week')]
	protected function getWeek(): int {
		return $this->_object->week;
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('dow')]
	protected function getDow(): int {
		return (int) $this->_object->dow;
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('dow_iso')]
	protected function getDowIso(): int {
		return (int) $this->_object->dow_iso;
	}

	/**
	 * @codeCoverageIgnore
	 * @return bool
	 */
	#[Property('is_weekend')]
	protected function getIsWeekend(): bool {
		return $this->_object->is_weekend;
	}

	/**
	 * @codeCoverageIgnore
	 * @return bool
	 */
	#[Property('is_weekday')]
	protected function getIsWeekday(): bool {
		return $this->_object->is_weekday;
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('doy')]
	protected function getDoy(): int {
		return $this->_object->doy;
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('year')]
	protected function getYear(): int {
		return $this->_object->year;
	}

	/**
	 * @param int $year
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('year')]
	protected function setYear(int $year): void {
		$this->_object->setDate($year, $this->month, $this->day);
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('month')]
	protected function getMonth(): int {
		return $this->_object->month;
	}

	/**
	 * @param int $month
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('month')]
	protected function setMonth(int $month): void {
		$this->_object->setDate($this->year, $month, $this->day);
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('day')]
	protected function getDay(): int {
		return $this->_object->day;
	}

	/**
	 * @param int $day
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('day')]
	protected function setDay(int $day): void {
		$this->_object->setDate($this->year, $this->month, $day);
	}
}
