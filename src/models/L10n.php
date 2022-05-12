<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 * @property $name Name of the locale (basically-read only, can be set only once)
 *
 * @property-read \spaf\simputils\models\Box|array $settings_date_time
 * @property-read \spaf\simputils\models\Box|array $settings_data_unit
 */
class L10n extends SimpleObject {
	use RedefinableComponentTrait;

	#[DebugHide]
	protected ?string $_name = null;

	#[Property('name')]
	protected function setName(string $val) {
		if (empty($this->_name)) {
			$this->_name = $val;
		}
	}

	#[Property('name')]
	protected function getName(): ?string {
		return $this->_name;
	}

	public static $is_auto_setup = true;

	#[Property('settings_date_time')]
	protected function getDateTime(): Box {
		return PHP::box($this->DateTime);
	}

	#[Property('settings_data_unit')]
	protected function getDataUnit(): Box {
		return PHP::box($this->DataUnit);
	}

	protected $DateTime = [
		'user_date_format' => DT::FMT_DATE,
		'user_time_format' => DT::FMT_TIME,

		'user_time_ext_format' => DT::FMT_TIME_EXT,
		'user_time_full_format' => DT::FMT_TIME_FULL,

		'user_datetime_format' => DT::FMT_DATETIME,
		'user_datetime_ext_format' => DT::FMT_DATETIME_EXT,
		'user_datetime_full_format' => DT::FMT_DATETIME_FULL,

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
			// MARK Maybe sync it with DT::
			date_default_timezone_set($this->DateTime['user_default_tz']);
		}

		$class = PHP::redef(DataUnit::class);
		PHP::metaMagicSpell($class, 'l10n', $this->DataUnit);
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_L10N; // @codeCoverageIgnore
	}
}
