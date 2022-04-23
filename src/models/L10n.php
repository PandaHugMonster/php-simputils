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
