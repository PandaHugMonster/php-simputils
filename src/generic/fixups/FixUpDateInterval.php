<?php

namespace spaf\simputils\generic\fixups;

use spaf\simputils\models\InitConfig;
use spaf\simputils\traits\RedefinableComponentTrait;
use spaf\simputils\traits\SimpleObjectTrait;

/**
 * @codeCoverageIgnore
 */
class FixUpDateInterval extends \DateInterval {
	use SimpleObjectTrait;
	use RedefinableComponentTrait;

	public $y;
	public $m;
	public $d;
	public $h;
	public $i;
	public $s;
	public $f;
	public $invert;
	public $days;
	public $weekday;
	public $weekday_behavior;
	public $first_last_day_of;
	public $special_type;
	public $special_amount;
	public $have_weekday_relative;
	public $have_special_relative;

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATE_INTERVAL;
	}
}
