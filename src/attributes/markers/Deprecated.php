<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\generic\BasicAttribute;

/**
 * Deprecated marker
 *
 * Almost exactly the same as the one provided by JetBrains
 *
 * @codeCoverageIgnore
 */
#[Attribute]
class Deprecated extends BasicAttribute {

	/**
	 * @param string|null $reason      Reason
	 * @param string|null $replacement Suggested replacement
	 */
	public function __construct(
		public ?string $reason = null,
		public ?string $replacement = null
	) {}
}
