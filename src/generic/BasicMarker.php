<?php

namespace spaf\simputils\generic;

use Attribute;
use spaf\simputils\attributes\Property;
use spaf\simputils\models\Box;

/**
 * @property Box|array|null $tags
 * @property ?string        $reference Link to details/report or Responsible person's
 * contact information that can explain, help or give some additional info about
 * the marked entity.
 */
#[Attribute]
abstract class BasicMarker extends BasicAttribute {

	#[Property]
	protected Box|array|null $_tags = null;

	#[Property]
	protected ?string $_reference = null;

	public function __construct(
		Box|array|null $tags = null,
		?string $reference = null,
	) {
		$this->tags = $tags;
		$this->reference = $reference;
	}

}
