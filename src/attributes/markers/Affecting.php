<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\generic\BasicAttribute;

/**
 * This attribute should mark affecting methods
 *
 * Affecting methods - means they modify/affect the object they have been called on.
 *
 * Usually in such cases to "turn" affecting method to simple one use "clone()" method in a chain
 * beforehand.
 *
 * ```php
 *
 * $box = bx([1, 2, 3]);
 *
 * // Affecting usage (will modify content of the original box object)
 * $box->each(fn($v) => ["text_$v"]);
 * // Now $box contains:
 * //   ["text_1", "text_2", "text_3"]
 *
 * ```
 *
 * To make it "non-affecting" do the next things:
 * ```php
 * $box = bx([1, 2, 3]);
 *
 * // Non-affecting usage (will NOT modify content of the original box object)
 * $new_box = $box->clone()
 *      ->each(fn($v) => ["text_$v"]);
 * // As before $box contains:
 * //   [1, 2, 3]
 * // But $new_box contains:
 * //   ["text_1", "text_2", "text_3"]
 *
 * ```
 *
 * It's basically just a marker
 *
 *
 * @codeCoverageIgnore
 */
#[Deprecated(
	'Sub-Optimal design and purpose. Similar functionality is available as ' .
	'`ObjState(ObjState::TYPE_AFFECTING)`',
	'\spaf\simputils\attributes\markers\ObjState(ObjState::TYPE_AFFECTING)'
)]
#[Attribute(Attribute::TARGET_METHOD)]
class Affecting extends BasicAttribute {

}
