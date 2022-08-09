<?php

namespace spaf\simputils\components\execenvs;

use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\ExecEnvHandlerInterface;
use spaf\simputils\Str;

/**
 * This is just an example of the custom Exec-Env handler.
 * It will return true or false that is specified from `$what_to_return`
 * field. Might be in rare cases useful for debugging.
 *
 */
class DummyExecEnvHandler extends SimpleObject implements ExecEnvHandlerInterface {

	const EE_DEFAULT_NAME = 'dummy-exec-env';

	public function __construct(
		public bool $what_to_return = true,
		public string $ee_name = self::EE_DEFAULT_NAME,
		public bool $include_signature = true,
	) {}

	public function is(string $val): bool {
		return $this->what_to_return;
	}

	public function __toString(): string {
		$res = Str::from($this->what_to_return);
		if ($this->include_signature) {
			return "{$this->ee_name}#{$res}";
		}

		return "{$this->ee_name}";
	}

}
