<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\BasicMarker;
use spaf\simputils\models\Box;

/**
 * @property ?string $type
 * @property ?string $description
 */
#[Attribute]
class ObjState extends BasicMarker {

	const TYPE_AFFECTING = 'affecting';
	const TYPE_PARTIALLY_AFFECTING = 'partially-affecting';
	const TYPE_UNAFFECTING = 'unaffecting';

	#[Property]
	protected ?string $_type = null;

	#[Property]
	protected ?string $_description = null;

	public function __construct(
		?string $type = null,
		?string $description = null,
		Box|array|null $tags = null,
		?string $reference = null,
	) {
		$this->type = $type;
		$this->description = $description;
		parent::__construct($tags, $reference);
	}

}
