<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Extract;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\IPParsingException;
use spaf\simputils\generic\BasicIP;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\Math;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\ComparablesTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use ValueError;
use function chr;
use function intval;
use function ord;
use function preg_match;

/**
 * IP version 4 class
 *
 * @property-read int $octet1 First octet
 * @property-read int $octet2 Second octet
 * @property-read int $octet3 Third octet
 * @property-read int $octet4 Fourth octet
 * @property-read ?string $mask Mask if available
 * @property-read ?int $mask_cidr Mask CIDR if available
 */
class IPv4 extends BasicIP implements UrlCompatible {
	use ComparablesTrait;
	use RedefinableComponentTrait;

	#[DebugHide(false)]
	#[Extract(false)]
	protected ?string $storage = null;
	public bool $output_with_mask = true;

	#[Property('octet1')]
	protected function getOctetOne(): int {
		return ord($this->storage[0]);
	}

	#[Property('octet2')]
	protected function getOctetTwo(): int {
		return ord($this->storage[1]);
	}

	#[Property('octet3')]
	protected function getOctetThree(): int {
		return ord($this->storage[2]);
	}

	#[Property('octet4')]
	protected function getOctetFour(): int {
		return ord($this->storage[3]);
	}

	#[Property('mask_cidr')]
	protected function getMaskCidr(): ?int {
		$val = ord($this->storage[4]);
		return $val > 0?$val:null;
	}

	#[Property('mask')]
	protected function getMask(): ?string {
		if ($m = $this->getMaskCidr()) {
			$sub_res = Str::mul('1', $m).Str::mul('0', 32 - $m);
			$res = '';
			foreach (Str::div($sub_res, 8) as $octet) {
				if (!empty($res)) {
					$res .= '.';
				}
				$res .= Math::bin2dec($octet);
			}

			return $res;
		}
		return null;
	}

	function __construct(string $ip, bool $output_with_mask = true) {
		$parsed = static::parse($ip);
		if (!$parsed) {
			throw new IPParsingException('Couldn\'t parse the IPv4 string');
		}

		$this->output_with_mask = $output_with_mask;
		$this->storage = $parsed;
	}

	/**
	 * Parses the string of IPv4 into a "5-byte-string"
	 *
	 * Keep in mind that returned result either "null" if parsing failed,
	 * or 5-byte-string where each 1 byte is a value (0-255). And the last (fifth)
	 * byte contains mask, or 0.
	 *
	 * This is more internal method, recommended to avoid using it. It is being left
	 * public in the rare case somebody might need it.
	 *
	 * @param string $ip String representation of "IPv4"
	 *
	 * @return string|null
	 */
	static function parse(string $ip): ?string {
		$matches = [];
		$res = preg_match('#^([01]?[0-9]{2}|2(?:5[0-5]|[0-4][0-9])|[0-9]?)\.' .
			'([01]?[0-9]{2}|2(?:5[0-5]|[0-4][0-9])|[0-9]?)\.' .
			'([01]?[0-9]{2}|2(?:5[0-5]|[0-4][0-9])|[0-9]?)\.' .
			'([01]?[0-9]{2}|2(?:5[0-5]|[0-4][0-9])|[0-9]?)(?:/(\d+))?$#S', $ip, $matches);

		if ($res) {
			$mask = 0;
			if (!empty($matches[5])) {
				$mask = $matches[5] > 32?32:intval($matches[5]);
			}

			return
				chr(intval($matches[1])).
				chr(intval($matches[2])).
				chr(intval($matches[3])).
				chr(intval($matches[4])).
				chr($mask);
		}

		return null;
	}

	static function validate(string $ip): bool {
		return (bool) static::parse($ip);
	}

	public function __toString(): string {
		$res = $this->getOctetOne().'.'.
			$this->getOctetTwo().'.'.
			$this->getOctetThree().'.'.
			$this->getOctetFour();
		$mask = $this->getMaskCidr();
		if ($this->output_with_mask && $mask) {
			$res .= '/'.$mask;
		}
		return $res;
	}

	static function getOctetNames(): Box|array {
		return PHP::box(['octet1', 'octet2', 'octet3', 'octet4']); // @codeCoverageIgnore
	}

	/**
	 * @param mixed ...$args Arguments
	 *
	 * @throws \spaf\simputils\exceptions\IPParsingException IP parsing error
	 */
	private function getLeftAndRight(mixed ...$args) {
		/** @var static $right */
		$right = $args[0];
		$left = $this;

		if (Str::is($right)) {
			$right = new static($right);
		}
		if (!$right instanceof static) {
			throw new ValueError('Wrong object type');
		}

		return [$left, $right];
	}

	function equalsTo(...$args): bool {
		/** @var static $left */
		/** @var static $right */
		[$left, $right] = $this->getLeftAndRight(...$args);

		$res = true;
		foreach (static::getOctetNames() as $key) {
			$res = $res && $left->$key === $right->$key;
			if (!$res) {
				break;
			}
		}

		return $res;
	}

	function greaterThan(...$args): bool {
		/** @var static $left */
		/** @var static $right */
		[$left, $right] = $this->getLeftAndRight(...$args);

		foreach (static::getOctetNames() as $key) {
			if ($left->$key > $right->$key) {
				return true;
			} else if ($left->$key < $right->$key) {
				break;
			}
		}

		return false;
	}

	function lessThan(...$args): bool {
		/** @var static $left */
		/** @var static $right */
		[$left, $right] = $this->getLeftAndRight(...$args);

		foreach (static::getOctetNames() as $key) {
			if ($left->$key < $right->$key) {
				return true;
			} else if ($left->$key > $right->$key) {
				break;
			}
		}

		return false;
	}

	function range(IPv4|string $ip2) {
		$class = PHP::redef(IPv4Range::class);
		return new $class($this, $ip2);
	}

	function getHost($protocol): ?string {
		return "{$this}"; // @codeCoverageIgnore
	}

	function getPath($protocol): ?string {
		return null; // @codeCoverageIgnore
	}

	function getParams($protocol): ?string {
		return null; // @codeCoverageIgnore
	}

	function getData($protocol): ?string {
		return null; // @codeCoverageIgnore
	}

	function setFromStr($str, $mask_cidr, $output_with_mask): static {
		$this->__construct("{$str}/{$mask_cidr}", $output_with_mask);
		return $this;
	}

	function ___serialize(): Box|array {
		$owm = $this->output_with_mask;
		$this->output_with_mask = false;
		$res = [
			'value' => "{$this}",
			'mask_cidr' => "{$this->mask_cidr}",
			'output_with_mask' => $owm
		];
		$this->output_with_mask = $owm;
		return $res;
	}

	protected function ___deserialize(array|Box $data): static {
		return $this->setFromStr($data['value'], $data['mask_cidr'], $data['output_with_mask']);
	}

	/**
	 * @inheritDoc
	 */
	public static function redefComponentName(): string {
		return InitConfig::REDEF_IPV4;
	}
}
