<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\generic\fixups\FixUpDatePeriod;
use spaf\simputils\PHP;

/**
 * @property-read DateInterval $extended_interval
 * @property DateTime          $start
 * @property DateTime          $end
 *
 */
class DatePeriod extends FixUpDatePeriod {

	private $_cached_extended_interval = null;

	/**
	 * @codeCoverageIgnore
	 * @return Box|array
	 */
	function ___serialize(): Box|array {
		return [
			'start' => "{$this->start->for_system}",
			'end' => "{$this->end->for_system}",
			'interval' => "{$this->extended_interval->specification_string}",
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
		$this->start = $data['start'];
		$this->end = $data['end'];
		$this->interval = $data['interval'];

		return $this;
	}

	/**
	 *
	 * TODO Add "interval" part to the string
	 * @return string
	 */
	function __toString(): string {
		return "{$this->start} - {$this->end}";
	}

	/**
	 * Extended interval provides access to the framework version interval
	 *
	 * This is the only way to get the framework DateInterval object, because it was not possible
	 * to replace the native php "interval" functionality.
	 *
	 * So prefrably use this one instead of native "interval" property.
	 *
	 * @return DateInterval
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented Redefinition is not implemented
	 */
	#[Property('extended_interval')]
	#[Shortcut('interval')]
	protected function getExtendedInterval(): DateInterval {
		if (empty($this->_cached_extended_interval)) {
			$class_i = PHP::redef(DateInterval::class);

			$spec = DT::dateIntervalSpecificationString($this->interval);
			$this->_cached_extended_interval = new $class_i($spec);
		}

		return $this->_cached_extended_interval;
	}
}
