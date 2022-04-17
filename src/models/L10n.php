<?php

namespace spaf\simputils\models;

use spaf\simputils\DT;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use spaf\simputils\traits\RedefinableComponentTrait;

class L10n extends SimpleObject {
	use RedefinableComponentTrait;

	public static $is_auto_setup = true;

	protected $DateTime = [
		'user_date_format' => DT::FMT_DATE,
		'user_time_format' => DT::FMT_TIME,
		'user_datetime_format' => DT::FMT_DATETIME,
		'user_default_tz' => 'UTC',
	];

	protected $DataUnit = [
		'translations' => []
	];

	/**
	 * Apply those settings to other classes
	 *
	 * @return void
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented Redefinable component is not defined
	 */
	public function doSetUp() {
		$class = PHP::redef(DateTime::class);
		PHP::metaMagicSpell($class, 'l10n', $this->DateTime);
		if (!empty($this?->DateTime['user_default_tz'])) {
			date_default_timezone_set($this->DateTime['user_default_tz']);
		}

		$class = PHP::redef(DataUnit::class);
		PHP::metaMagicSpell($class, 'l10n', $this->DataUnit);
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_L10N; // @codeCoverageIgnore
	}
}
