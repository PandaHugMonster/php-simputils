<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\generic\BasicAttribute;

/**
 * This attribute should mark methods that are being shortcuts.
 *
 * Meaning that any method that is not being directly containing any logic, but sub-supplying
 * the arguments further to the target method
 *
 * It's basically just a marker
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Shortcut extends BasicAttribute {

	/**
	 * @param ?string $target FQN to the function/method. Basically just a string ref
	 */
	public function __construct(
		public ?string $target,
		public ?string $comment = null,
	) {}
}