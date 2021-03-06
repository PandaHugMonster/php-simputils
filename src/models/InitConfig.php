<?php

namespace spaf\simputils\models;

use spaf\simputils\components\initblocks\DotEnvInitBlock;
use spaf\simputils\generic\BasicInitConfig;

/**
 *
 */
class InitConfig extends BasicInitConfig {

	public null|array|Box $init_blocks = [
		DotEnvInitBlock::class,
	];

}
