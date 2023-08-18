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
 * @property int         $hour   Hours
 * @property int         $minute Minutes
 * @property int         $second Seconds
 *
 * @property-read int    $milli  Milliseconds, at most 3 digits
 * @property int         $micro  Microseconds at most 6 digits
 */
class Time extends FixUpDateTimePrism {

	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->_object->getForSystemObj()->for_system;
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		$obj = $this->_object;

		return $obj->format(DateTime::$l10n_user_time_format);
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
	#[Property('hour')]
	protected function getHour(): int {
		return $this->_object->hour;
	}

	/**
	 * @param int $hour
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('hour')]
	protected function setHour(int $hour): void {
		$this->_object->setTime($hour, $this->minute, $this->second, $this->micro);
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('minute')]
	protected function getMinute(): int {
		return $this->_object->minute;
	}

	/**
	 * @param int $minute
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('minute')]
	protected function setMinute(int $minute): void {
		$this->_object->setTime($this->hour, $minute, $this->second, $this->micro);
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('second')]
	protected function getSecond(): int {
		return $this->_object->second;
	}

	/**
	 * @param int $second
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('second')]
	protected function setSecond(int $second): void {
		$this->_object->setTime($this->hour, $this->minute, $second, $this->micro);
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('micro')]
	protected function getMicro(): int {
		return $this->_object->micro;
	}

	/**
	 * @param int $micro
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('micro')]
	protected function setMicro(int $micro): void {
		$this->_object->setTime($this->hour, $this->minute, $this->second, $micro);
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	#[Property('milli')]
	protected function getMilli(): int {
		return $this->_object->milli;
	}
}
