<?php

namespace spaf\simputils\generic\fixups;

use DatePeriod;
use spaf\simputils\models\InitConfig;
use spaf\simputils\traits\MetaMagic;
use spaf\simputils\traits\PropertiesTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

class FixUpDatePeriod extends DatePeriod {
	use PropertiesTrait;
	use RedefinableComponentTrait;
	use MetaMagic;

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_PERIOD;
	}
}
