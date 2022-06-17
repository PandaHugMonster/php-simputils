<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\Str;

/**
 * @property-read IPv4 $start
 * @property-read IPv4 $end
 */
class IPv4Range extends SimpleObject {

	#[Property]
	protected ?IPv4 $_start = null;

	#[Property]
	protected ?IPv4 $_end = null;

	function __construct(string|IPv4 $ip1, string|IPv4 $ip2) {
		// FIX  Implement dynamic class!
		if (Str::is($ip1)) {
			$ip1 = new IPv4($ip1);
		}
		if (Str::is($ip2)) {
			$ip2 = new IPv4($ip2);
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

	public function __toString(): string {
		return "{$this->_start} - {$this->_end}";
	}
}
