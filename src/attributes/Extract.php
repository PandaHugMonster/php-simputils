<?php

namespace spaf\simputils\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Extract {

	public function __construct(
		public bool $enabled = true,
		public ?string $comment = null,
	) {}
}
