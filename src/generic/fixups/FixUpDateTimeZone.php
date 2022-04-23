<?php

namespace spaf\simputils\generic\fixups;


use DateTimeZone;
use spaf\simputils\models\InitConfig;
use spaf\simputils\traits\RedefinableComponentTrait;
use spaf\simputils\traits\SimpleObjectTrait;

/**
 * @codeCoverageIgnore
 */
class FixUpDateTimeZone extends DateTimeZone {
	use SimpleObjectTrait;
	use RedefinableComponentTrait;

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_TIME_ZONE;
	}
}
