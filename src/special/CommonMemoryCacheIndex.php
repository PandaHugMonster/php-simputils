<?php

namespace spaf\simputils\special;

use spaf\simputils\components\validators\BooleanValidator;
use spaf\simputils\components\validators\DateTimeValidator;
use spaf\simputils\components\validators\FloatValidator;
use spaf\simputils\components\validators\IntegerValidator;
use spaf\simputils\components\validators\StringValidator;
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
		'string' => StringValidator::class,
		'bool' => BooleanValidator::class,
		'int' => IntegerValidator::class,
		'float' => FloatValidator::class,
		'DateTime' => DateTimeValidator::class,
	];
}
