<?php

namespace spaf\simputils\models;

use Closure;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\CallableAbsent;
use spaf\simputils\traits\RedefinableComponentTrait;
use function is_null;
use function password_hash;
use function password_verify;
use function trigger_error;
use const PASSWORD_BCRYPT;
use const PASSWORD_DEFAULT;

/**
 * Class to store password in a slightly more secure way than just a string
 *
 * @property-read ?callable $hashing_callable
 * @property-read ?callable $hashing_verify_callable
 * @property-read ?string   $hash
 */
class Password extends Secret {
	use RedefinableComponentTrait;

	static $default_hashing_algo = PASSWORD_BCRYPT;

	static $global_hashing_callable = null;

	static $global_hashing_verify_callable = null;

	#[DebugHide]
	#[Property(type: 'get')]
	protected $_hashing_callable = null;

	#[DebugHide]
	#[Property(type: 'get')]
	protected $_hashing_verify_callable = null;

	#[DebugHide]
	protected ?string $_hash = null;

	function __construct(
		$value = null,
		$name = null,
		$hashing_callable = null,
		$hashing_verify_callable = null,
		$type = 'password',
	) {
		parent::__construct($value, $name, $type);
		$this->_hashing_callable = $hashing_callable
			?? static::$global_hashing_callable
			?? Closure::fromCallable([static::class, 'defaultHashingCallable']);
		$this->_hashing_verify_callable = $hashing_verify_callable
			?? static::$global_hashing_verify_callable
			?? Closure::fromCallable([static::class, 'defaultHashingVerifyCallable']);
	}

	static function defaultHashingCallable(string $password, ...$args): string {
		if (static::$default_hashing_algo != PASSWORD_DEFAULT) {
			trigger_error(
				"\$default_hashing_algo differs from PASSWORD_DEFAULT constant. ".
				"This could be due to change of the PHP version and suggested encryption algo. ".
				"Or if you have specified a custom encryption algo.",
			);
		}

		return password_hash($password, static::$default_hashing_algo, $args);
	}

	static function defaultHashingVerifyCallable(string $password, string $hash): bool {
		return password_verify($password, $hash);
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_PASSWORD;
	}

	function verifyPassword(string $password): bool {
		if (empty($this->_hashing_verify_callable)) {
			throw new CallableAbsent('$hashing_check_callable is not specified');
		}
		$callable = $this->_hashing_verify_callable;

		return $callable($password, $this->hash);
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

	/**
	 * @throws CallableAbsent
	 */
	#[DebugHide(false)]
	#[Property('hash')]
	protected function getHash(): string {
		if (empty($this->_hash)) {
			$this->_hash = $this->genHash();
		}

		return $this->_hash;
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

		$this->_hash = $callable($this->value, ...$args);

		return $this->_hash;
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
