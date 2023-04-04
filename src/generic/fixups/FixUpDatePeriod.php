<?php

namespace spaf\simputils\generic\fixups;

use DatePeriod;
use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\traits\RedefinableComponentTrait;
use spaf\simputils\traits\SimpleObjectTrait;

/**
 * @codeCoverageIgnore
 */
class FixUpDatePeriod extends DatePeriod {
	use SimpleObjectTrait;
	use RedefinableComponentTrait;

	public static function redefComponentName(): string {
		return AppInitConfig::REDEF_DATE_PERIOD;
	}
}
