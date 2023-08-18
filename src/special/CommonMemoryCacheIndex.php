<?php

namespace spaf\simputils\special;

use spaf\simputils\components\normalizers\BooleanNormalizer;
use spaf\simputils\components\normalizers\BoxNormalizer;
use spaf\simputils\components\normalizers\DataUnitNormalizer;
use spaf\simputils\components\normalizers\DateTimeNormalizer;
use spaf\simputils\components\normalizers\FloatNormalizer;
use spaf\simputils\components\normalizers\IntegerNormalizer;
use spaf\simputils\components\normalizers\IPNormalizer;
use spaf\simputils\components\normalizers\LowerCaseNormalizer;
use spaf\simputils\components\normalizers\StringNormalizer;
use spaf\simputils\components\normalizers\UpperCaseNormalizer;
use spaf\simputils\components\normalizers\UrlNormalizer;
use spaf\simputils\components\normalizers\VersionNormalizer;
use spaf\simputils\models\Box;
use spaf\simputils\models\PhpInfo;

class CommonMemoryCacheIndex {

	/**
	 * The very initial "getenv()" state.
	 *
	 * IMP  Please make a ticket if this causes troubles on multi-thread systems
	 *
	 * @var Box|array|null
	 */
	static null|Box|array $initial_get_env_state = null;

	static ?PhpInfo $default_phpinfo_object = null;
	static ?string $original_phpinfo_string = null;
	// NOTE ILP - Infinite Loop Prevention
	static ?array $to_array_ilp_storage = null;

	const PROPERTY_VALIDATOR_DISABLED = 0;
	const PROPERTY_VALIDATOR_LIMITED = 1;
	const PROPERTY_VALIDATOR_ENABLED = 2;

	/**
	 * Levels:
	 *  0 - validators/normalizers/etc disabled
	 *  1 - auto - disabled, but explicitly specified validators/normalizers/etc enabled
	 *  2 - All the validators/normalizers/etc enabled (auto and explicit)
	 * @var int
	 */
	static int $property_validators_enabled = 2;

	static ?array $property_validators = [
		'string' => StringNormalizer::class,
		'bool' => BooleanNormalizer::class,
		'int' => IntegerNormalizer::class,
		'float' => FloatNormalizer::class,
		'DateTime' => DateTimeNormalizer::class,
		'DataUnit' => DataUnitNormalizer::class,
		'IPv4' => IPNormalizer::class,
		'UrlObject' => UrlNormalizer::class,
		'Box' => BoxNormalizer::class,
		'Version' => VersionNormalizer::class,

		'lower' => LowerCaseNormalizer::class,
		'upper' => UpperCaseNormalizer::class,
	];
}
