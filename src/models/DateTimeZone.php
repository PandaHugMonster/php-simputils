<?php

namespace spaf\simputils\models;

use Exception;
use spaf\simputils\generic\fixups\FixUpDateTimeZone;
use spaf\simputils\Str;

class DateTimeZone extends FixUpDateTimeZone {

	/**
	 * @codeCoverageIgnore
	 * @return Box|array
	 */
	function ___serialize(): Box|array {
		return [
			'value' => "{$this}",
		];
	}

	/**
	 * @param array|Box $data
	 *
	 * @return $this
	 * @throws Exception
	 * @codeCoverageIgnore
	 */
	protected function ___deserialize(array|Box $data): static {
		return $this->setFromData($data);
	}

	/**
	 * @param $data
	 *
	 * @codeCoverageIgnore
	 * @return $this
	 * @throws Exception
	 */
	function setFromData($data): static {
		$this->__construct($data['value']);

		return $this;
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
