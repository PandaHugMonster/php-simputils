<?php

namespace spaf\simputils\attributes;

use Attribute;
use spaf\simputils\generic\BasicAttribute;
use spaf\simputils\logger\Logger;

#[Attribute]
class Deprecated extends BasicAttribute {

	protected ?string $reason = null;
	protected ?string $replacement = null;
	public ?string $target_representation = null;

	/**
	 * @param string|null $reason
	 * @param string|null $replacement
	 */
	public function __construct(?string $reason = null, ?string $replacement = null) {
		$this->reason = $reason;
		$this->replacement = $replacement;
	}

	public function run() {
		Logger::warning(
			'Usage of "%s" is deprecated. %s %s %s',
			$this->target_representation,
			$this->reason,
			empty($this->reason) || empty($this->replacement)?'':'/',
			$this->replacement
		);
	}
}
