<?php

namespace spaf\simputils\generic\fixups;

use DatePeriod;
use spaf\simputils\models\InitConfig;
use spaf\simputils\traits\RedefinableComponentTrait;
use spaf\simputils\traits\SimpleObjectTrait;

/**
 * @codeCoverageIgnore
 */
class FixUpDatePeriod extends DatePeriod {
	use SimpleObjectTrait;
	use RedefinableComponentTrait;

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_PERIOD;
	}
}
