<?php

namespace spaf\simputils\generic\fixups;

use DateInterval;
use DateTimeInterface;
use spaf\simputils\generic\BasicPrism;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\DateTimeZone;
use spaf\simputils\PHP;
use spaf\simputils\traits\ForOutputsTrait;

/**
 * @codeCoverageIgnore
 */
abstract class FixUpDateTimePrism extends BasicPrism {
	use ForOutputsTrait;

	public function __construct(DateTime|string $datetime = "now", ?DateTimeZone $timezone = null) {
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

	public function __toString(): string {
		return $this->getForUser();
	}
}
