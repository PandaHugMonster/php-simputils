<?php

namespace spaf\simputils\components\dotenv\models;

use spaf\simputils\models\H10lConf;
use spaf\simputils\models\InitConfig;

class EnvVars extends H10lConf {


	static function redefComponentName(): string {
		return InitConfig::REDEF_ENV_VARS; // @codeCoverageIgnore
	}
}
