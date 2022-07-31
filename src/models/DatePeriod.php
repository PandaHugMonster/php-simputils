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

	/**
	 * Extended interval provides access to the framework version interval
	 *
	 * This is the only way to get the framework DateInterval object, because it was not possible
	 * to replace the native php "interval" functionality.
	 *
	 * So prefrably use this one instead of native "interval" property.
	 *
	 * @return \spaf\simputils\models\DateInterval
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

	function setFromData($start, $end, $interval): static {
		$this->start = $start;
		$this->end = $end;
		$this->interval = $interval;
		return $this;
	}

	function ___serialize(): Box|array {
		return [
			'start' => "{$this->start->for_system}",
			'end' => "{$this->end->for_system}",
			'interval' => "{$this->extended_interval->specification_string}",
		];
	}

	protected function ___deserialize(array|Box $data): static {
		return $this->setFromData($data['start'], $data['end'], $data['interval']);
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
