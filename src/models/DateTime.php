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
 * @property-read string $date
 * @property-read string $time
 */
class DateTime extends \DateTime {
	use PropertiesTrait;
	use RedefinableComponentTrait;

	#[Property('date')]
	protected function getDateStr(): string {
		return $this->format(DT::FMT_DATE);
	}

	#[Property('time')]
	protected function getTimeStr(): string {
		return $this->format(DT::FMT_TIME);
	}

	public function __toString(): string {
		return DT::stringify($this);
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_TIME;
	}
}
