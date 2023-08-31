<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\attributes\Property;
use spaf\simputils\components\normalizers\VersionNormalizer;
use spaf\simputils\generic\BasicAttribute;
use spaf\simputils\models\Version;

/**
 * Deprecated marking
 *
 * Almost exactly the same as the one provided by JetBrains
 *
 * @codeCoverageIgnore
 * @property ?string $reason
 * @property ?string $replacement
 * @property Version|string|null $since
 * @property Version|string|null $removed
 */
#[Attribute]
class Deprecated extends BasicAttribute {

	#[Property]
	protected ?string $_reason = null;

	#[Property]
	protected ?string $_replacement = null;

	#[Property(valid: VersionNormalizer::class)]
	protected Version|string|null $_since = null;

	#[Property(valid: VersionNormalizer::class)]
	protected Version|string|null $_removed = null;

	/**
	 * @param string|null $reason      Reason
	 * @param string|null $replacement Suggested replacement
	 */
	public function __construct(
		?string $reason = null,
		?string $replacement = null,
		Version|string|null $since = null,
		Version|string|null $removed = null,
	) {
		$this->reason = $reason;
		$this->replacement = $replacement;
		$this->since = $since;
		$this->removed = $removed;
	}
}
