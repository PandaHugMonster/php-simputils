<?php

namespace spaf\simputils\interfaces;

use spaf\simputils\models\Box;

/**
 * Interface to implement ExecEnvHandler functionality
 *
 */
interface ExecEnvHandlerInterface {

	/**
	 * Checking whether Exec-Env value is matching
	 *
	 * @param Box|array|string $val
	 *
	 * @return bool
	 */
	function is(Box|array|string $val): bool;

	public function __toString(): string;
}
