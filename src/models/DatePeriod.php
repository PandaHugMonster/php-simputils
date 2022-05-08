<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\generic\fixups\FixUpDatePeriod;
use spaf\simputils\PHP;

/**
 * @property-read \spaf\simputils\models\DateInterval $extended_interval
 * @property \spaf\simputils\models\DateTime $start
 * @property \spaf\simputils\models\DateTime $end
 *
 */
class DatePeriod extends FixUpDatePeriod {

	private $_cached_extended_interval = null;

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

	/**
	 *
	 * TODO Add "interval" part to the string
	 * @return string
	 */
	public function __toString(): string {
		return "{$this->start} - {$this->end}";
	}
}
