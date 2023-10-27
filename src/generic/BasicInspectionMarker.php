<?php

namespace spaf\simputils\generic;

use Attribute;
use spaf\simputils\attributes\Property;
use spaf\simputils\models\Box;

/**
 * @property ?string $severity
 * @property ?string $comment
 */
#[Attribute(Attribute::IS_REPEATABLE)]
class BasicInspectionMarker extends BasicMarker {

	const SEVERITY_UNDEFINED = 'undefined';
	const SEVERITY_LOW = 'low';
	const SEVERITY_MEDIUM = 'medium';
	const SEVERITY_HIGH = 'high';
	const SEVERITY_CRITICAL = 'critical';

	#[Property]
	protected ?string $_severity = null;

	#[Property]
	protected ?string $_comment = null;

	public function __construct(
		?string $comment,
		?string $severity = self::SEVERITY_UNDEFINED,
		Box|array|null $tags = null,
		?string $reference = null
	) {
		$this->comment = $comment;
		$this->severity = $severity;
		parent::__construct($tags, $reference);
	}

}
