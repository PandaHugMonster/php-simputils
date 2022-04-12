<?php

namespace spaf\simputils\traits;

use spaf\simputils\attributes\Property;
use spaf\simputils\models\SystemFingerprint;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\dsf\DsfVersionsMethodsTrait;
use ValueError;

/**
 * @codeCoverageIgnore Unfinished
 */
trait DefaultSystemFingerprintTrait {
	use DsfVersionsMethodsTrait;

	public null|Version $version;
	public int $strictness = 0;

	/**
	 * @inheritdoc
	 * @return string[]
	 */
	#[Property('parts')]
	public function getParts(): array {
		return [
			'version', 'strictness',
		];
	}

	/**
	 * @inheritdoc
	 * @return string
	 */
	#[Property('name')]
	public function getName(): string {
		return static::NAME;
	}

	/**
	 * @return mixed
	 */
	#[Property('data')]
	public function getData(): mixed {
		$method = $this->versionApplicableMethodChoose($this->version);
		return $method();
	}

	public function __construct(...$params) {
		$this->version = $params['version'] ?? $params[0] ?? PHP::simpUtilsVersion();
		parent::__construct(...$params);
	}

	/**
	 * Version to local private method name conversion
	 *
	 * 1.0.0     => version_1_0_0
	 * 1.0.0RC55 => version_1_0_0
	 * 1.2.3RC2  => version_1_2_3
	 *
	 * **Important:** Version build release and revision are being ignored
	 *
	 * @param Version|string $version Version object
	 *
	 * @return string
	 */
	private static function autoPrepareMethodName(Version|string $version): string {
		$class = PHP::redef(Version::class);

		if (Str::is($version)) {
			$version = new $class($version);
		}
		$res = "version_{$version->major}_{$version->minor}_{$version->patch}";

		return $res;
	}

	/**
	 * @param string $field Field/Property name
	 * @param mixed  $val   Value
	 *
	 * @codeCoverageIgnore
	 * @return mixed
	 */
	public function preCheckProperty(string $field, mixed $val): mixed {
		$version_class = PHP::redef(Version::class);
		if ($field === 'version') {
			if (empty($val)) {
				throw new ValueError('Version parameter/property must be specified');
			} else if (Str::is($val)) {
				return new $version_class($val);
			} else if (!PHP::classContains($val, $version_class)) {
				throw new ValueError('Version object is not a correct one');
			}
		}

		return parent::preCheckProperty($field, $val);
	}

	/**
	 * Checks if `$this` fingerprint is fitting the `$val` fingerprint
	 *
	 * @param SystemFingerprint|string|null $val    The target fingerprint against of which check
	 *                                              is performed
	 * @param bool                          $strict Strict parsing, if set to true - checks exact
	 *                                              match!
	 *
	 * @return bool True if `$this` is fitting to `$val`, false otherwise
	 *
	 */
	public function fits(mixed $val, bool $strict = false): bool {
		if (is_null($val)) {
			return false;
		}
		if (Str::is($val)) {
			$val = SystemFingerprint::parse($val);
		}

		if ($strict) {
			return strval($val) === strval($this);
		}

		$fp_base_1 = $this->generateString(true);
		$fp_base_2 = $val->generateString(true);

		if ($fp_base_1 === $fp_base_2) {
			return true;
		}

		return false;
	}
}
