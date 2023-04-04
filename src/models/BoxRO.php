<?php

namespace spaf\simputils\models;

use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\exceptions\ReadOnlyProblem;
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

	function popFromEnd(): mixed {
		throw new ReadOnlyProblem('Popping from the end is not ' .
			'possible due to read-only state');
	}

	function popFromStart(): mixed {
		throw new ReadOnlyProblem('Popping from the start is not ' .
			'possible due to read-only state');
	}

	static function redefComponentName(): string {
		return AppInitConfig::REDEF_BRO; // @codeCoverageIgnore
	}
}
