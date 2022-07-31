<?php

namespace spaf\simputils\generic\fixups;

use DateInterval;
use DateTimeInterface;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\BasicPrism;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\DateTimeZone;
use spaf\simputils\PHP;
use spaf\simputils\traits\ForOutputsTrait;

/**
 * @codeCoverageIgnore
 *
 * @property \spaf\simputils\models\DateTimeZone|string $tz Timezone change
 */
abstract class FixUpDateTimePrism extends BasicPrism {
	use ForOutputsTrait;

	public function __construct(
		DateTime|string $datetime = "now",
		null|string|DateTimeZone $timezone = null
	) {
		$class_dt = PHP::redef(DateTime::class);
		if ($datetime instanceof DateTime) {
			$this->init($datetime);
		} else {
			$this->init(new $class_dt($datetime, $timezone));
		}
	}

	public function add(DateInterval|string $interval): static {
		$this->_object->add($interval);
		return $this;
	}

	public function sub(DateInterval|string $interval): static {
		$this->_object->sub($interval);
		return $this;
	}

	public function modify(string $modifier) {
		$this->_object->modify($modifier);
		return $this;
	}

	public function diff(DateTimeInterface $targetObject, bool $absolute = false) {
		return $this->_object->diff($targetObject, $absolute);
	}

	#[Property('tz')]
	public function getTimezone(): DateTimeZone|false {
		return new DateTimeZone($this->_object->getTimezone()->getName());
	}

	#[Property('tz', type: 'set')]
	public function setTimezone($timezone) {
		$this->_object->tz = $timezone;
		return $this;
	}

	public function __toString(): string {
		return $this->getForUser();
	}
}
