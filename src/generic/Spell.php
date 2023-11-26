<?php

namespace spaf\simputils\generic;

use Attribute;

/**
 * Meta-Magic Spell BaseClass
 */
#[Attribute]
abstract class Spell extends BasicAttribute {

	/**
	 * Name of the spell class.
	 *
	 * Basically a short string name of the Spell
	 *
	 * @return mixed
	 */
	abstract static function getName(): string;

	/**
	 * The method that will be invoked for this spell
	 *
	 * @param callable $target
	 * @param mixed    ...$spell
	 *
	 * @return mixed
	 */
	abstract static function invoke(callable $target, ...$spell): mixed;

}
