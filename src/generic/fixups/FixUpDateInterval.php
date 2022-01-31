<?php

namespace spaf\simputils\generic\fixups;

use spaf\simputils\models\InitConfig;
use spaf\simputils\traits\MetaMagic;
use spaf\simputils\traits\PropertiesTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

class FixUpDateInterval extends \DateInterval {
	use PropertiesTrait;
	use RedefinableComponentTrait;
	use MetaMagic;

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
