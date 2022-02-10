<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace spaf\simputils\models;

use Exception;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use function bcadd;
use function bccomp;
use function gmp_add;
use function gmp_cmp;

/**
 * BigNumber representation class
 *
 * The BigNumber can use any of the main big-number libraries "GMP" or "BCMath".
 * Basically it's just a proxy with extended functionality.
 *
 * In context of description of methods here the value of this object might be referred to as
 * `$a`
 *
 * Example:
 * ```php
 *
 * use spaf\simputils\models\BigNumber;
 * use spaf\simputils\PHP;
 * use function spaf\simputils\basic\pr;
 *
 * PHP::init(['l10n' => 'RU']);
 *
 * $n = new BigNumber('5');
 *
 * pr("$n", "{$n->add(10)->add(200)->mul("10000")->div('5')}");
 *
 * // Output would be:
 * //   5
 * //   430000
 *
 * ```
 *
 * and here is example with huge numbers:
 * ```php
 *
 * use spaf\simputils\models\BigNumber;
 * use spaf\simputils\PHP;
 * use function spaf\simputils\basic\pr;
 *
 * PHP::init(['l10n' => 'RU']);
 *
 * $n = new BigNumber('1000000000000000000000000000000000000000000000000');
 *
 * pr("$n", "{$n->add(10)->add(200)->mul("10000")->div('5')}");
 *
 * // Output would be:
 * //   1000000000000000000000000000000000000000000000000
 * //   2000000000000000000000000000000000000000000000420000
 *
 * ```
 *
 * TODO Implemented only limited amount of common functionality between gmp and bcmath.
 *      Should be implemented all the GMP similar functionality.
 *
 * @see https://www.php.net/manual/en/intro.gmp.php
 * @see https://www.php.net/manual/en/intro.bc.php
 */
class BigNumber extends SimpleObject {

	const SUBSYSTEM_GMP = 'gmp';
	const SUBSYSTEM_BCMATH = 'bcmath';

	protected $_ext;
	protected $_value;

	/**
	 * Create BigNumber object
	 *
	 * @param int|static|string $val       String or integer value representing number or big-number
	 * @param ?string           $extension Enforcing usage of particular extension for this number
	 *
	 * @throws \Exception Exception if no math extension is installed
	 */
	public function __construct(int|self|string $val = 0, ?string $extension = null) {
		$this->_ext = static::checkExtensionAvailability($extension);
		if ($this->_ext === false) {
			throw new Exception('No math extension available');
		}

		$this->_value = $val;
	}

	/**
	 * Check whether extension(s) are available
	 *
	 * Without argument will return name of the available extension, with the argument will
	 * return that extension name if available (enforced extension).
	 *
	 * @param string|null $extension Preferred extension
	 *
	 * @return string|false Returns name of the available extension or false if no available
	 *                      extension
	 */
	public static function checkExtensionAvailability(?string $extension = null): string|false {
		$php_info = PHP::info();
		$gmp_available = false;
		$bcmath_available = false;

		if ($php_info->hasExtension(static::SUBSYSTEM_GMP)) {
			if (empty($extension) || $extension === static::SUBSYSTEM_GMP) {
				return static::SUBSYSTEM_GMP;
			}
			$gmp_available = true;
		}
		if ($php_info->hasExtension(static::SUBSYSTEM_BCMATH)) {
			if (empty($extension) || $extension === static::SUBSYSTEM_BCMATH) {
				return static::SUBSYSTEM_BCMATH;
			}
			$bcmath_available = true;
		}

		if ($gmp_available) {
			return static::SUBSYSTEM_GMP;
		} else if ($bcmath_available) {
			return static::SUBSYSTEM_BCMATH;
		}

		return false;
	}

	/**
	 * Addition `+`
	 *
	 * `$a + $b`
	 *
	 * @param self|string|int $b
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_add()
	 * @see \bcadd()
	 */
	#[Shortcut('\gmp_add()|\bcadd()')]
	public function add(self|string|int $b): static {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_add($this->_value, "$b"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcadd($this->_value, "$b"));
		}
	}

	/**
	 * Subtraction `-`
	 *
	 * `$a - $b`
	 *
	 * @param self|string|int $b
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_sub()
	 * @see \bcsub()
	 */
	#[Shortcut('\gmp_sub()|\bcsub()')]
	public function sub(self|string|int $b): self {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_sub($this->_value, "$b"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcsub($this->_value, "$b"));
		}
	}

	/**
	 * Division `/`
	 *
	 * `$a / $b`
	 *
	 * @param self|string|int $b
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_div_q()
	 * @see \bcdiv()
	 */
	#[Shortcut('\gmp_div_q()|\bcdiv()')]
	public function div(self|string|int $b): static {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_div_q($this->_value, "$b"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcdiv($this->_value, "$b"));
		}
	}

	/**
	 * Multiplication `*`
	 *
	 * `$a * $b`
	 *
	 * @param self|string|int $b
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_mul()
	 * @see \bcmul()
	 */
	#[Shortcut('\gmp_mul()|\bcmul()')]
	public function mul(self|string|int $b): self {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_mul($this->_value, "$b"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcmul($this->_value, "$b"));
		}
	}

	/**
	 * Modulo operation
	 *
	 * @param self|string|int $modulo
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_mod()
	 * @see \bcmod()
	 */
	#[Shortcut('\gmp_mod()|\bcmod()')]
	public function mod(self|string|int $modulo): self {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_mod($this->_value, "$modulo"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcmod($this->_value, "$modulo"));
		}
	}

	/**
	 * Comparison
	 *
	 * @param self|string|int $b
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_cmp()
	 * @see \bccomp()
	 */
	#[Shortcut('\gmp_cmp()|\bccomp()')]
	public function cmp(self|string|int $b): static {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_cmp($this->_value, "$b"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bccomp($this->_value, "$b"));
		}
	}

	/**
	 * Power
	 *
	 * @param self|string|int $exponent
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_pow()
	 * @see \bcpow()
	 */
	#[Shortcut('\gmp_pow()|\bcpow()')]
	public function pow(self|string|int $exponent): static {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_pow($this->_value, "$exponent"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcpow($this->_value, "$exponent"));
		}
	}

	/**
	 * Power with modulo
	 *
	 * @param self|string|int $exponent
	 * @param self|string|int $modulo
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_powm()
	 * @see \bcpowmod()
	 */
	#[Shortcut('\gmp_powm()|\bcpowmod()')]
	public function powMod(self|string|int $exponent, self|string|int $modulo): static {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_powm($this->_value, "$exponent", "$modulo"));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcpowmod($this->_value, "$exponent", "$modulo"));
		}
	}

	/**
	 * Square Root
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_sqrt()
	 * @see \bcsqrt()
	 */
	#[Shortcut('\gmp_sqrt()|\bcsqrt()')]
	public function sqrt(): static {
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			return new static(gmp_sqrt($this->_value));
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			return new static(bcsqrt($this->_value));
		}
	}

	public function __toString(): string {
		return "{$this->_value}";
	}
}
