<?php

namespace spaf\simputils\interfaces;

/**
 * Interface to implement ExecEnvHandler functionality
 *
 */
interface ExecEnvHandlerInterface {

	/**
	 * Checking whether Exec-Env value is matching
	 *
	 * @param string $val
	 *
	 * @return bool
	 */
	function is(string $val): bool;

	public function __toString(): string;
}
