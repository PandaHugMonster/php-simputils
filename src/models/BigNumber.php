<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace spaf\simputils\models;

use Exception;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function bcadd;
use function bccomp;
use function gmp_add;
use function gmp_cmp;
use function intval;
use function preg_replace;

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
 * FIX  Consider implementing "Decimal" extension support https://github.com/php-decimal
 * FIX  Possibly implement hacks for "floated point numbers" for GMP
 *
 * @see https://www.php.net/manual/en/intro.gmp.php
 * @see https://www.php.net/manual/en/intro.bc.php
 * @see https://github.com/php-decimal
 *
 * @property bool $mutable
 */
class BigNumber extends SimpleObject {

	const SUBSYSTEM_GMP = 'gmp';
	const SUBSYSTEM_BCMATH = 'bcmath';
	// const SUBSYSTEM_DECIMAL = 'decimal';

	public static $default_extension = self::SUBSYSTEM_GMP;

	protected $_is_mutable;
	protected $_ext;
	protected $_value;

	/**
	 * Create BigNumber object
	 *
	 * @param int|static|string $val       String or integer value representing number or big-number
	 * @param bool              $mutable   If set to true, then all the operations will be changing
	 *                                     value of this object
	 * @param ?string           $extension Enforcing usage of particular extension for this number
	 *
	 * @throws \Exception Exception if no math extension is installed
	 */
	public function __construct(
		int|self|float|string $val = 0,
		bool $mutable = false,
		?string $extension = null
	) {
		$this->_is_mutable = $mutable;
		$this->_ext = static::checkExtensionAvailability($extension);
		if ($this->_ext === false) {
			throw new Exception('No math extension available');
		}
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = intval($val);
		}
		$this->_value = $val;
	}

	#[Property('mutable')]
	public function getMutable(): bool {
		return $this->_is_mutable;
	}

	#[Property('mutable')]
	public function setMutable(bool $val) {
		$this->_is_mutable = $val;
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
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 * @see \gmp_add()
	 * @see \bcadd()
	 */
	#[Shortcut('\gmp_add()|\bcadd()')]
	public function add(self|string|int $b, ?bool $mutable = null): static {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = gmp_add($this->_value, "$b");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcadd($this->_value, "$b");
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	/**
	 * Subtraction `-`
	 *
	 * `$a - $b`
	 *
	 * @param self|string|int $b
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_sub()
	 * @see \bcsub()
	 */
	#[Shortcut('\gmp_sub()|\bcsub()')]
	public function sub(self|string|int $b, ?bool $mutable = null): self {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = gmp_sub($this->_value, "$b");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcsub($this->_value, "$b");
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	/**
	 * Division `/`
	 *
	 * `$a / $b`
	 *
	 * @param self|string|int $b
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_div_q()
	 * @see \bcdiv()
	 */
	#[Shortcut('\gmp_div_q()|\bcdiv()')]
	public function div(self|string|float|int $b, ?bool $mutable = null): static {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			// $b = intval($b);
			$val = gmp_div_q($this->_value, "$b");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcdiv($this->_value, $b, scale: 2);
			$val = Str::removeEnding($val, '.00');
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	public function floor() {
		$mutable = $mutable ?? $this->_is_mutable;
		$res = preg_replace('/([^.]*)([.]?.*)$/', '$1', "{$this->_value}");
		if ($mutable) {
			$this->_value = $res;
			$res = $this;
		} else {
			$res = new static($res, extension: $this->_ext);
		}
		return $res;
	}

	/**
	 * Multiplication `*`
	 *
	 * `$a * $b`
	 *
	 * @param self|string|int $b
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_mul()
	 * @see \bcmul()
	 */
	#[Shortcut('\gmp_mul()|\bcmul()')]
	public function mul(self|string|int $b, ?bool $mutable = null): self {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = gmp_mul($this->_value, "$b");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcmul($this->_value, "$b");
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	/**
	 * Modulo operation
	 *
	 * @param self|string|int $modulo
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_mod()
	 * @see \bcmod()
	 */
	#[Shortcut('\gmp_mod()|\bcmod()')]
	public function mod(self|string|int $modulo, ?bool $mutable = null): self {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = gmp_mod($this->_value, "$modulo");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcmod($this->_value, "$modulo");
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	public function isZero(): bool {
		$res = $this->_value === 0 || $this->_value === '0';
		return $res;
	}

	/**
	 * Comparison
	 *
	 * @param self|string|int $b
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_cmp()
	 * @see \bccomp()
	 */
	#[Shortcut('\gmp_cmp()|\bccomp()')]
	public function cmp(self|string|int|float $b): int {

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$b = intval($b);
			$val = gmp_cmp($this->_value, "$b");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bccomp($this->_value, "$b");
		}

		return $val;
	}

	/**
	 * Power
	 *
	 * @param self|string|int $exponent
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_pow()
	 * @see \bcpow()
	 */
	#[Shortcut('\gmp_pow()|\bcpow()')]
	public function pow(self|string|int $exponent, ?bool $mutable = null): static {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = gmp_pow($this->_value, "$exponent");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcpow($this->_value, "$exponent");
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	/**
	 * Power with modulo
	 *
	 * @param self|string|int $exponent
	 * @param self|string|int $modulo
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_powm()
	 * @see \bcpowmod()
	 */
	#[Shortcut('\gmp_powm()|\bcpowmod()')]
	public function powMod(
		self|string|int $exponent,
		self|string|int $modulo,
		?bool $mutable = null
	): static {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = gmp_powm($this->_value, "$exponent", "$modulo");
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcpowmod($this->_value, "$exponent", "$modulo");
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	/**
	 * Square Root
	 *
	 * @param bool|null       $mutable If set to true of false, then overrides
	 *                                 the default "is mutable" flag of the object
	 *                                 for this operation
	 *
	 * @return static
	 * @throws \Exception
	 *
	 * @see \gmp_sqrt()
	 * @see \bcsqrt()
	 */
	#[Shortcut('\gmp_sqrt()|\bcsqrt()')]
	public function sqrt(?bool $mutable = null): static {
		$mutable = $mutable ?? $this->_is_mutable;

		$val = null;
		if ($this->_ext === static::SUBSYSTEM_GMP) {
			$val = gmp_sqrt($this->_value);
		} else if ($this->_ext === static::SUBSYSTEM_BCMATH) {
			$val = bcsqrt($this->_value);
		}
		if ($mutable) {
			$this->_value = $val;
			$val = $this;
		} else {
			$val = new static($val, extension: $this->_ext);
		}

		return $val;
	}

	public function __toString(): string {
		return "{$this->_value}";
	}
}
