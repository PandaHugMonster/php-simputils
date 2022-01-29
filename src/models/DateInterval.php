<?php

namespace spaf\simputils\models;

use ReturnTypeWillChange;
use spaf\simputils\generic\fixups\FixUpDateInterval;

class DateInterval extends FixUpDateInterval {

	protected function formatForString() {
		$res = '%R';
		$arr = [
			'%y year' => (int) $this->format('%y'),
			'%m month' => (int) $this->format('%m'),
			'%d day' => (int) $this->format('%d'),
			'%h hour' => (int) $this->format('%h'),
			'%i minute' => (int) $this->format('%i'),
			'%s second' => (int) $this->format('%s'),
			'%f microsecond' => (int) $this->format('%f'),
		];
		foreach ($arr as $k => $v) {
			if ($v === 0) {
				continue;
			}
			$res .= ' '.$k.($v > 1?'s':'');
		}
		return $res;
	}

	#[ReturnTypeWillChange]
	public static function createFromDateString(string $datetime): static {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return static::expandFrom(parent::createFromDateString($datetime), new static('P1D'));
	}

	public function __toString(): string {
		return $this->format($this->formatForString());
	}
}
