<?php

namespace spaf\simputils\attributes;

use Attribute;
use spaf\simputils\exceptions\NotSupported;
use spaf\simputils\generic\BasicOutputControlAttribute;
use spaf\simputils\models\Box;

/**
 *
 * TODO Extract on classes to globally set the value (For example to disable all the
 *      properties output, and activate it on per property explicitly)
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Extract extends BasicOutputControlAttribute  {

	public function __construct(
		public bool $enabled = true,
		public ?string $comment = null,
	) {}

	/**
	 * @inheritDoc
	 */
	function appliedOnClass(): ?Box {
		throw new NotSupported(
			'Extract attribute is not supported on classes (at least for now)'
		);
	}

	/**
	 * @inheritDoc
	 */
	function appliedOnProperty(mixed $value = null): null|string|bool {
		return $this->enabled;
	}

	/**
	 * @inheritDoc
	 */
	function isApplicable(bool $extract_attr_on, bool $debug_hide_attr_on): bool {
		return $extract_attr_on;
	}
}
