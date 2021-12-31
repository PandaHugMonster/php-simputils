<?php

namespace spaf\simputils\attributes;

use Attribute;
use spaf\simputils\generic\BasicAttribute;

/**
 * Property attribute for methods
 *
 * Allowing to turn your class-methods into fields/properties
 *
 * You can turn your 1 method into both: setter and getter, or you can use 2 separate methods
 * for setter and getter. **The behaviour is identified by "parameters" and "return type".**
 *
 * **SETTER**:  If no first parameter is specified - then it's not a setter.
 *
 * **GETTER**:  If return-type is omitted or it's of type "void" or "never" - then this method
 *              will not be a "getter".
 *
 * **BOTH**:    If both conditions above met, then the same method will be used for both, and there
 *              will be second parameter specifying `TYPE_SET` or `TYPE_GET` constant value,
 *              indicating which particular case is called, so you could do `if ... else ...`
 *              for getter and for setter.
 *
 * @package spaf\simputils\attributes
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Property extends BasicAttribute {

	const TYPE_SET = 'set';
	const TYPE_GET = 'get';
	const TYPE_BOTH = 'both';

	const MODIFIER_PUBLIC = 'public';
	const MODIFIER_PROTECTED = 'protected';
	const MODIFIER_PRIVATE = 'private';

	/**
	 * @param string|null $name         Property name
	 * @param string|null $type         Enforced property type (get, set, both)
	 * @param bool        $debug_output By default true, if set, then `__debugInfo()` will include
	 *                                  property to the output. If false - value will be replaced
	 *                                  with a "cap". The mostly useful for cases when getter will
	 *                                  cause heavy calculation, network traffic, or files reading.
	 */
	public function __construct(
		public ?string $name = null,
		public ?string $type = null,
		public bool $debug_output = true,
	) {}
}
