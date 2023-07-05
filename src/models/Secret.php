<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function is_null;

/**
 * Class to store secret in a slightly more secure way than just a string
 *
 * @property ?string $name The recognizable name to associate with this secret.
 *                         Strongly recommended to specify it if exists.
 * @property ?string $type Custom type of the secret. Can be adjusted
 * @property ?string $value The secret value. It should be used carefully!
 */
class Secret extends SimpleObject {
	use RedefinableComponentTrait;
	use ForOutputsTrait;

	#[DebugHide(false)]
	#[Property]
	protected ?string $_value;

	#[Property]
	protected ?string $_type;

	#[Property]
	protected ?string $_name;

	function __construct($value = null, $name = null, $type = 'secret') {
		$this->_value = $value;
		$this->_name = $name;
		$this->_type = $type;
	}

	static function redefComponentName(): string {
		return InitConfig::REDEF_SECRET;
	}

	#[DebugHide(false)]
	#[Property('for_system')]
	protected function getForSystem(): string {
		return "{$this->value}";
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		$name = $this->_name;
		if (empty($name)) {
			$name = $this->type ?? 'secret';
		} else {
			$name = "S:{$name}";
		}
		if (!is_null($this->_value)) {
			return "**[{$name}]**";
		}

		return "";
	}
}
