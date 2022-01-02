<?php

namespace spaf\simputils\interfaces;

use spaf\simputils\generic\BasicInitConfig;

interface InitBlockInterface {

	/**
	 * The very first method invoked on a block object during init stage.
	 *
	 * @return bool Must always return true or false. If true - initialization was fine,
	 *              if false - means the initialization has failed. In this case block object
	 *              will not be added in `$successful_init_blocks`
	 */
	public function initBlock(BasicInitConfig $config): bool;
}
