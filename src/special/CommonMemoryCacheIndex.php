<?php

namespace spaf\simputils\special;

use spaf\simputils\components\normalizers\BooleanNormalizer;
use spaf\simputils\components\normalizers\DataUnitNormalizer;
use spaf\simputils\components\normalizers\DateTimeNormalizer;
use spaf\simputils\components\normalizers\FloatNormalizer;
use spaf\simputils\components\normalizers\IntegerNormalizer;
use spaf\simputils\components\normalizers\StringNormalizer;
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
	public static null|Box|array $initial_get_env_state = null;

	public static ?PhpInfo $default_phpinfo_object = null;
	public static ?string $original_phpinfo_string = null;
	// NOTE ILP - Infinite Loop Prevention
	public static ?array $to_array_ilp_storage = null;

	public static int $property_validators_enabled = 2;
	public static ?array $property_validators = [
		'string' => StringNormalizer::class,
		'bool' => BooleanNormalizer::class,
		'int' => IntegerNormalizer::class,
		'float' => FloatNormalizer::class,
		'DateTime' => DateTimeNormalizer::class,
		'DataUnit' => DataUnitNormalizer::class,
	];
}
