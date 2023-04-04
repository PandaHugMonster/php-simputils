<?php

namespace spaf\simputils\generic\fixups;


use DateTime;
use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\traits\RedefinableComponentTrait;
use spaf\simputils\traits\SimpleObjectTrait;

/**
 * @codeCoverageIgnore
 */
class FixUpDateTime extends DateTime {
	use SimpleObjectTrait;
	use RedefinableComponentTrait;

	public static function redefComponentName(): string {
		return AppInitConfig::REDEF_DATE_TIME;
	}
}
