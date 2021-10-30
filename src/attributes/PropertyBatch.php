<?php

namespace spaf\simputils\attributes;

use Attribute;
use spaf\simputils\generic\BasicAttribute;

/**
 * @package spaf\simputils\attributes
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class PropertyBatch extends BasicAttribute {

	const TYPE_SET = 'set';
	const TYPE_GET = 'get';
	const TYPE_BOTH = 'both';

	const MODIFIER_PUBLIC = 'public';
	const MODIFIER_PROTECTED = 'protected';
	const MODIFIER_PRIVATE = 'private';

	/**
	 * This is relevant only for ArrayObject similar objects,
	 * then the values will be stored inside of the object (like ArrayObject enables)
	 */
	const STORAGE_SELF = '#SELF';

	public function __construct(
		public ?string $type = null,
		public ?string $modifier = null,
		public ?string $storage = null,
		public ?array $names = null,
	) {}
}
