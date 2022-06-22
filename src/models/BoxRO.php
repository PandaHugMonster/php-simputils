<?php

namespace spaf\simputils\models;

use spaf\simputils\traits\ArrayReadOnlyAccessTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 * Immutable Box (for short: bro)
 *
 * Simple usage:
 * ```php
 *  $bro = new BoxRO(['new', 'stuff', 'be']);
 * ```
 */
class BoxRO extends Box {
	use ArrayReadOnlyAccessTrait;
	use RedefinableComponentTrait;

	static function redefComponentName(): string {
		return InitConfig::REDEF_BRO; // @codeCoverageIgnore
	}
}
