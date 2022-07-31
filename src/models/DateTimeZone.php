<?php

namespace spaf\simputils\models;

use spaf\simputils\generic\fixups\FixUpDateTimeZone;
use spaf\simputils\Str;

class DateTimeZone extends FixUpDateTimeZone {

	function setFromStr($str): static {
		$this->__construct($str);
		return $this;
	}

	function ___serialize(): Box|array {
		return [
			'value' => "{$this}",
		];
	}

	protected function ___deserialize(array|Box $data): static {
		return $this->setFromStr($data['value']);
	}

	public function __toString(): string {
		return $this->getName(); // @codeCoverageIgnore
	}

	/**
	 * @codeCoverageIgnore
	 * @return array
	 */
	public function __debugInfo() {
		$res = [];
		$res['name'] = $this->getName();

		if (Str::upper($res['name']) !== 'UTC') {
			$res['location'] = $this->getLocation();
		}

		return $res;
	}
}
