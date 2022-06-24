<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\Property;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\Box;
use spaf\simputils\PHP;

/**
 * @property-read ?string $protocol
 */
abstract class BasicProtocolProcessor extends SimpleObject {

	#[Property(type: 'get')]
	protected ?string $_protocol = null;

	function __construct(string $protocol) {
		$this->_protocol = $protocol;
	}

	abstract function parse(UrlCompatible|string|Box|array $value, bool $is_preparsed = false);

	abstract function generateForSystem($host, $path, $params, $data): string;

	abstract function generateForUser($host, $path, $params, $data): string;

	abstract function generateRelative($host, $path, $params, $data): string;

	function __toString(): string {
		return PHP::objToNaiveString($this, ['protocol' => $this->_protocol]);//@codeCoverageIgnore
	}
}
