<?php

namespace spaf\simputils\generic;

use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\Box;
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;

abstract class SubAppInitConfig extends InitConfig implements InitBlockInterface {

	public null|array|Box $init_blocks = [];

	public function initBlock(BasicInitConfig $config) {
		return (bool) PHP::init($this); // @codeCoverageIgnore
	}
}
