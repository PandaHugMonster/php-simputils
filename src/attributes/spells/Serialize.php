<?php

namespace spaf\simputils\attributes\spells;


use Attribute;
use spaf\simputils\generic\Spell;

#[Attribute(Attribute::TARGET_METHOD)]
class Serialize extends Spell {

	static function getName(): string {
		return 'serialize';
	}

}
