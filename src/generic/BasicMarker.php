<?php

namespace spaf\simputils\generic;

use Attribute;
use spaf\simputils\attributes\Property;
use spaf\simputils\models\Box;

/**
 * @property Box|array|null $tags
 * @property ?string $author
 */
#[Attribute]
abstract class BasicMarker extends BasicAttribute {

	#[Property]
	protected Box|array|null $_tags = null;

	#[Property]
	protected ?string $_author = null;

	public function __construct(
		Box|array|null $tags = null,
		?string $author = null
	) {
		$this->tags = $tags;
		$this->author = $author;
	}

}
