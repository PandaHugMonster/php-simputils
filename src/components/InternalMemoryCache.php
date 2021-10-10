<?php

namespace spaf\simputils\components;

use spaf\simputils\models\PhpInfo;

class InternalMemoryCache {

	public static ?PhpInfo $default_phpinfo_object = null;
	public static ?string $original_phpinfo_string = null;

}
