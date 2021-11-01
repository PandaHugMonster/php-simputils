<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\generic\BasicAttribute;

/**
 * @codeCoverageIgnore
 */
#[Attribute]
class Deprecated extends BasicAttribute {

	protected ?string $reason = null;
	protected ?string $replacement = null;
	public ?string $target_representation = null;

	/**
	 * @param string|null $reason      Reason
	 * @param string|null $replacement Suggested replacement
	 */
	public function __construct(?string $reason = null, ?string $replacement = null) {
		$this->reason = $reason;
		$this->replacement = $replacement;
	}
}
