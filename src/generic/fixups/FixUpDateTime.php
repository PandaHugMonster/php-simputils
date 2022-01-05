<?php

namespace spaf\simputils\generic\fixups;


use DateTime;
use spaf\simputils\models\InitConfig;
use spaf\simputils\traits\PropertiesTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

class FixUpDateTime extends DateTime {
	use PropertiesTrait;
	use RedefinableComponentTrait;

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_TIME;
	}
}
