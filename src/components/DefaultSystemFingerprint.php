<?php

namespace spaf\simputils\components;


use Exception;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\BasicSystemFingerprint;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use function is_string;

class DefaultSystemFingerprint extends BasicSystemFingerprint {

	const NAME = 'DSF';

	public null|Version $version;
	public int $strictness = 0;

	/**
	 * @inheritdoc
	 * @return string[]
	 */
	#[Property('parts')] public function getParts(): array {
		return [
			'version', 'strictness',
		];
	}

	/**
	 * @inheritdoc
	 * @return string
	 */
	#[Property('name')] public function getName(): string {
		return static::NAME;
	}

	/**
	 * @return mixed
	 */
	#[Property('data')] public function getData(): mixed {
		return [
			'TEST', 'QUQU'
		];
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
}
