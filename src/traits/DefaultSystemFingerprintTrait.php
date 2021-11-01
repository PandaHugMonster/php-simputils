<?php

namespace spaf\simputils\traits;

use Exception;
use spaf\simputils\attributes\Property;
use spaf\simputils\components\SystemFingerprint;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\Settings;
use spaf\simputils\traits\dsf\DsfVersionsMethodsTrait;

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
		$this->version = $params['version'] ?? $params[0] ?? Settings::version();
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
		if (is_string($version)) {
			$version = new Version($version);
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
	 * @throws \Exception Exception value is wrong
	 */
	public function preCheckProperty(string $field, mixed $val): mixed {
		if ($field === 'version') {
			if (empty($val)) {
				throw new Exception('Version parameter/property must be specified');
			} else if (is_string($val)) {
				return new Version($val);
			} else if (!PHP::classContains($val, Version::class)) {
				throw new Exception('Version object is not a correct one');
			}
		}

		return parent::preCheckProperty($field, $val);
	}

	public function fit(SystemFingerprint|string|null $val, bool $strict = false): bool {
		if (is_null($val)) {
			return false;
		}
		if (is_string($val)) {
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
