<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 * @property-read IPv4 $start
 * @property-read IPv4 $end
 */
class IPv4Range extends SimpleObject {
	use RedefinableComponentTrait;

	#[Property]
	protected ?IPv4 $_start = null;

	#[Property]
	protected ?IPv4 $_end = null;

	function __construct(string|IPv4 $ip1, string|IPv4 $ip2) {
		$class = PHP::redef(IPv4::class);

		if (Str::is($ip1)) {
			$ip1 = new $class($ip1); // @codeCoverageIgnore
		}
		if (Str::is($ip2)) {
			$ip2 = new $class($ip2);
		}
		$ip1->output_with_mask = false;
		$ip2->output_with_mask = false;

		if ($ip1->lt($ip2)) {
			$this->_start = $ip1;
			$this->_end = $ip2;
		} else {
			$this->_start = $ip2;
			$this->_end = $ip1;
		}
	}

	function setFromData($data): static {
		$this->__construct($data['start'], $data['end']);
		return $this;
	}

	function ___serialize(): Box|array {
		return [
			'start' => "{$this->start}",
			'end' => "{$this->end}",
		];
	}

	protected function ___deserialize(array|Box $data): static {
		return $this->setFromData($data);
	}

	public function __toString(): string {
		return "{$this->_start} - {$this->_end}";
	}

	/**
	 * @inheritDoc
	 */
	public static function redefComponentName(): string {
		return AppInitConfig::REDEF_IPV4_RANGE;
	}
}
