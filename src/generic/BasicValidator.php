<?php

namespace spaf\simputils\generic;

use spaf\simputils\interfaces\ValidatorInterface;
use function is_null;

/**
 * @codeCoverageIgnore
 */
abstract class BasicValidator extends SimpleObject implements ValidatorInterface {

	/**
	 * Wrapper method that is used to permit null without processing
	 *
	 * For example if "null|..." or "?..." specified.
	 *
	 * NOTE Really important to note that in case of "not-allowed", it means that
	 *      null value goes directly to the "process" method. And then the exact
	 *      normalizer has to deal with it.
	 *
	 * TODO One of the reasons why it's done like this, that the registry with methods
	 *      attached to properties do not have stored additional arguments from
	 *      "properties" attributes pre-processing. When more advanced reference
	 *      storage will be built, this could be done differently.
	 *
	 * @param mixed $value Value
	 *
	 * @return mixed|null
	 */
	static function whenNullAllowed(mixed $value): mixed {
		if (is_null($value)) {
			return null;
		}
		return static::process($value);
	}
}
