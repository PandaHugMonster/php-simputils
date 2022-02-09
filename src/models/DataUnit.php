<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\Data;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 *
 * FIX  Add functionality for fractions
 */
class DataUnit extends SimpleObject {
	use RedefinableComponentTrait;
	use ForOutputsTrait;

	const USER_FORMAT_HR = 'hr';

	public static $l10n_translations = null;

	#[DebugHide]
	protected int $_value;
	public string $user_format = self::USER_FORMAT_HR;

	public function __construct(string|int $value = 0) {
		$this->_value = Data::toBytes($value);
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->_value;
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		return $this->format();
	}

	/**
	 * Outputs the value in the specified format
	 *
	 * By default uses "user format"
	 *
	 * @param string|null $format
	 *
	 * @return string
	 * @throws \spaf\simputils\exceptions\NonExistingDataUnit
	 * @throws \spaf\simputils\exceptions\UnspecifiedDataUnit
	 */
	public function format(?string $format = null, bool $with_units = true): string {
		$format = $format ?? $this->user_format;
		if ($format === static::USER_FORMAT_HR) {
			return Data::humanReadable($this->_value);
		}
		return Data::bytesTo($this->_value, $format).($with_units?Str::upper($format):null);
	}

	/**
	 * Translates data-units
	 *
	 * An argument must be in initial "english" format. This translator is uni-directional,
	 * From english abbreviation to another language abbreviation
	 *
	 * @param string $name English abbreviation (as constant values)
	 *
	 * @return false|mixed
	 */
	public static function translator(string $name) {
		$name = Str::upper($name);
		$check = new Box(static::$l10n_translations);
		if ($check->containsKey($name)) {
			$name = $check[$name];
		}
		return $name;
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DATA_UNIT;
	}

	public function __toString(): string {
		return $this->for_user;
	}
}
