<?php

namespace spaf\simputils\components;

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;

/**
 * @property-read mixed $value
 * @property-read bool $is_disabled
 */
class RenderedWrapper extends SimpleObject {

	#[Property(type: 'get')]
	protected $_value = null;

	#[Property]
	protected $_is_disabled = false;

	function __construct($rendered_value) {
		$this->_value = $rendered_value;
	}

	function disabled($value) {
		$this->_is_disabled = $value;
		return $this;
	}

	public function __toString(): string {
		return "{$this->_value}";
	}
}
