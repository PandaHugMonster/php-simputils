<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\attributes\Property;
use spaf\simputils\models\Box;

/**
 * Duplicated marking
 *
 * @property null|Box|array $related Array of references to related places that
 * are duplicates
 */
#[Attribute]
class Duplicated extends Issue {

	const TYPE_DUPLICATED = 'duplicated';

	#[Property(type: 'get')]
	protected ?string $_type = self::TYPE_DUPLICATED;

	#[Property]
	protected ?string $_related = null;

	public function __construct(
		?string $comment,
		null|Box|array $related = null,
		?string $severity = self::SEVERITY_UNDEFINED,
		Box|array|null $tags = null,
		?string $reference = null
	) {
		parent::__construct($this->_type, $comment, $severity, $tags, $reference);
	}

}
