<?php

namespace spaf\simputils\components;

use spaf\simputils\models\Box;
use spaf\simputils\models\PhpInfo;

class InternalMemoryCache {

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

}
