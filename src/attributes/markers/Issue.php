<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\BasicInspectionMarker;
use spaf\simputils\models\Box;

/**
 * Issue marking
 *
 * @property ?string $type
 */
#[Attribute(Attribute::IS_REPEATABLE)]
class Issue extends BasicInspectionMarker {

	const TYPE_PERFORMANCE = 'performance';
	const TYPE_ARCHITECTURE = 'architecture';
	const TYPE_BUG = 'bug';
	const TYPE_MESSY = 'Messy';
	const TYPE_LACKS_DOCUMENTATION = 'lacks-documentation';
	const TYPE_COUNTER_INTUITIVE = 'counter-intuitive';
	const TYPE_WEIRD_BEHAVIOUR = 'weird-behaviour';

	#[Property]
	protected ?string $_type = null;

	public function __construct(
		?string $type,
		?string $comment,
		?string $severity = self::SEVERITY_UNDEFINED,
		Box|array|null $tags = null,
		?string $reference = null
	) {
		$this->type = $type;
		parent::__construct($comment, $severity, $tags, $reference);
	}

}
