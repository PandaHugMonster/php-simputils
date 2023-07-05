<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\CallableAbsent;
use spaf\simputils\traits\RedefinableComponentTrait;
use function is_null;

/**
 * Class to store password in a slightly more secure way than just a string
 *
 * @property-read ?callable $hashing_callable
 * @property-read ?callable $hashing_check_callable
 * @property-read ?string $hash
 */
class Password extends Secret {
	use RedefinableComponentTrait;

	#[Property(type: 'get')]
	protected $_hashing_callable = null;

	#[Property(type: 'get')]
	protected $_hashing_check_callable = null;

	#[DebugHide(false)]
	#[Property(type: 'get')]
	protected ?string $_hash = null;

	function __construct(
		$value = null,
		$name = null,
		$hashing_callable = null,
		$hashing_check_callable = null,
		$type = 'password'
	) {
		parent::__construct($value, $name, $type);
		$this->_hashing_callable = $hashing_callable;
		$this->_hashing_check_callable = $hashing_check_callable;
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_PASSWORD;
	}

	/**
	 * Generate hash value through custom callable and returns
	 *
	 * @param ...$args
	 *
	 * @return string
	 * @throws CallableAbsent
	 */
	function genHash(...$args): string {
		if (empty($this->_hashing_callable)) {
			throw new CallableAbsent('$hashing_callable is not specified');
		}
		$callable = $this->_hashing_callable;

		$this->_hash = $callable(...$args);
		return $this->_hash;
	}

	/**
	 * @throws CallableAbsent
	 */
	protected function getHash(): string {
		if (empty($this->_hash)) {
			return $this->genHash();
		}
		return $this->_hash;
	}

	function checkHash($against_hash): bool {
		if (empty($this->_hashing_check_callable)) {
			throw new CallableAbsent('$hashing_check_callable is not specified');
		}
		$callable = $this->_hashing_check_callable;

		return $callable($against_hash);
	}

	/**
	 * Explicitly assign hash value
	 *
	 * @param $hash
	 *
	 * @return self
	 */
	function assignHash($hash): self {
		$this->_hash = $hash;
		return $this;
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		$name = $this->_name;
		if (empty($name)) {
			$name = $this->_type ?? 'password';
		} else {
			$name = "P:{$name}";
		}
		if (!is_null($this->_value)) {
			return "**[{$name}]**";
		}

		return "";
	}
}
