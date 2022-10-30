<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\ExecEnvException;
use spaf\simputils\interfaces\ExecEnvHandlerInterface;
use spaf\simputils\models\Box;
use spaf\simputils\PHP;
use function intval;
use function is_null;
use function is_numeric;
use function preg_match;

/**
 * The very default Exec-Environment Handler implementation
 *
 * If you want to modify it, just extend from it, or implement
 * `\spaf\simputils\interfaces\ExecEnvHandlerInterface` interface
 *
 * @property-read ?string $value
 * @property bool $is_local
 * @property bool $is_hierarchical
 * @property Box $permitted_values
 */
class BasicExecEnvHandler extends SimpleObject implements ExecEnvHandlerInterface {

	const EE_UNKNOWN = 'unknown';

	const EE_DEV = 'dev';
	const EE_DEMO = 'demo';
	const EE_PROD = 'prod';

	const EE_DEV_LOCAL = 'dev-local';
	const EE_DEMO_LOCAL = 'demo-local';
	const EE_PROD_LOCAL = 'prod-local';

	#[Property]
	protected ?bool $_is_local = false;

	#[DebugHide]
	protected ?string $_ee = null;

	#[Property('value')]
	protected function getValue(): ?string {
		return $this->_ee;
	}

	#[Property]
	protected bool $_is_hierarchical = false;

	#[Property]
	protected Box|array $_permitted_values = [
		1 => self::EE_PROD,
		2 => self::EE_DEMO,
		3 => self::EE_DEV,
	];

	public function __construct(
		$ee = self::EE_UNKNOWN,
		$is_hierarchical = false,
		$permitted_values = null
	) {
		if (is_null($permitted_values)) {
			$permitted_values = $this->_permitted_values;
		}
		$this->_permitted_values = PHP::box($permitted_values);
		$this->_permitted_values->joined_to_str = true;

		$this->_is_hierarchical = $is_hierarchical;

		if ($ee === static::EE_UNKNOWN) {
			$this->_ee = static::EE_UNKNOWN;
			$this->_is_local = false;
		} else {
			[$this->_ee, $this->_is_local] = $this->parse($ee);
			$this->_is_local = (bool) $this->_is_local;
		}
	}

	protected function parse(string $val): array {
		$res = [];
		$permitted = $this->_permitted_values->clone();
		$permitted->separator = '|';
		$mask = PREG_UNMATCHED_AS_NULL;

		preg_match(
			"#({$permitted})?(-local)?#i",
			$val,
			$res,
			$mask
		);

		return PHP::box($res)->batch([1, 2], true);
	}

	/**
	 * @inheritDoc
	 *
	 * Keep in mind that current "local" Exec-Env will return true for non-local one.
	 *
	 * In non-hierarchical "dev-local" will return true for "dev" and "dev-local",
	 * but will return false for all other cases.
	 *
	 * In hierarchical "dev-local" will return in the same way but including the hierarchical
	 * model. So "prod", "prod-local", "demo", "demo-local", "dev" and "dev-local" will return
	 * true for "dev-local" Exec-Env, when for hierarchical "dev" true will be returned only
	 * for "prod", "demo" and "dev". None of local ones will be considered.
	 *
	 * If hierarchical model is enabled, then permitted values with lower index integer value
	 * will be included into the higher index integer values.
	 *
	 * Like this: `prod -> demo -> dev`, so in case of "demo" all the prod stuff will
	 * return true, but "dev" will return false. And in case of "dev" - both "prod"
	 * and "demo" will be included.
	 *
	 * By default hierarchical model is disabled
	 *
	 * @param string $val
	 * @param bool $is_hierarchical
	 * @param bool $is_local
	 *
	 * @return bool
	 * @throws \spaf\simputils\exceptions\ExecEnvException
	 */
	function is(string $val, bool $is_hierarchical = false, bool $is_local = false): bool {
		[$expected_ee, $expected_is_local] = $this->parse($val);
		$is_local = $expected_is_local || $is_local;

		if ($expected_ee === static::EE_UNKNOWN || $this->_ee === static::EE_UNKNOWN) {
			$p = $this->_permitted_values->clone();

			throw new ExecEnvException('Unknown exec-env cannot be used or checked. ' .
				"Please set the proper exec-env value: {$p}");
		}

		$check = $this->_is_local || $is_local === false;

		$is_hierarchical = (bool) $this->is_hierarchical || $is_hierarchical;

		if ($this->_ee === $expected_ee) {
			if ($check) {
				// NOTE Then does not matter if local or not
				return true;
			}
		} else if ($is_hierarchical) {
			// NOTE Hierarchical
			$flipped = $this->permitted_values->flipped();
			if (isset($flipped[$expected_ee]) && isset($flipped[$this->_ee])) {
				if (is_numeric($flipped[$expected_ee]) && is_numeric($flipped[$this->_ee])) {
					return intval($flipped[$expected_ee]) <= intval($flipped[$this->_ee])
						&& $check;
				}
			}
		}

		return false;
	}

	function __toString(): string {
		$res = $this->_ee;
		if ($this->_is_local) {
			$res = "{$res}-local";
		}
		return "{$res}";
	}

}
