<?php

namespace spaf\simputils\attributes;

use Attribute;
use ReflectionUnionType;
use spaf\simputils\generic\BasicAttribute;
use spaf\simputils\special\PropertiesCacheIndex;

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

	public static function subProcess(
		$obj,
		$item,
		$attr,
		$name,
		$call_type
	): array {
		$ref_name = $obj::class.'#'.$name;
		$ref_name_type = $ref_name.'#'.Property::TYPE_GET;
		$func_ref = $item->name;

		//// NOTE   Impossible to optimize and extract due to PHP limitations on passing by ref...
		if (!isset(PropertiesCacheIndex::$index[$ref_name_type])) {
			PropertiesCacheIndex::$index[$ref_name_type] = null;
		}
		$index = &PropertiesCacheIndex::$index;
		////

		$args = $attr->getArguments();

		if ($name === static::expectedName($func_ref, $attr, $args)) {
			$method_type = static::methodAccessType($item, $attr, $args);

			if ($method_type === Property::TYPE_BOTH) {
				if (!isset($index[$key_type = $ref_name.'#'.Property::TYPE_GET])) {
					$index[$key_type] = $func_ref;
				}
				if (!isset($index[$key_type = $ref_name.'#'.Property::TYPE_SET])) {
					$index[$key_type] = $func_ref;
				}

				return [$func_ref, true];
			} else if ($call_type === $method_type) {
				if ($method_type === Property::TYPE_GET) {
					if (
						!isset($index[$key_type = $ref_name.'#'.Property::TYPE_GET])
					) {
						$index[$key_type] = $func_ref;
					}

					return [$func_ref, true];
				} else if ($method_type === Property::TYPE_SET) {
					if (
						!isset($index[$key_type = $ref_name.'#'.Property::TYPE_SET])
					) {
						$index[$key_type] = $func_ref;
					}

					return [$func_ref, true];
				}
			}

			return [$func_ref, $method_type === Property::TYPE_GET?'read-only':'write-only'];
		}

		return [$func_ref, false];
	}

	public static function expectedName($func_ref, \ReflectionAttribute $attr, $args = null) {
		$args = $args ?? $attr->getArguments();
		return $args[0] ?? $args['name'] ?? $func_ref;
	}

	public static function methodAccessType($ref, \ReflectionAttribute $attr, $args = null) {
		// FIX  Optimize later
		$args = $args ?? $attr->getArguments();

		$method_type = $args[1] ?? $args['type'] ?? null;

		if (!empty($method_type)) {
			$method_type = strtolower($method_type);
		} else {
			$ref_ret_type = $ref?->getReturnType() ?? null;
			if ($ref_ret_type instanceof ReflectionUnionType) {
				$r = [];
				foreach ($ref_ret_type->getTypes() as $ref_ret_type_item) {
					$r[] = $ref_ret_type_item;
				}
				$return_type = implode('|', $r);
			} else {
				$return_type = $ref_ret_type?->getName() ?? null;
			}

			$is_setter = (bool) $ref->getNumberOfParameters()
				|| $return_type === 'void'
				|| $return_type === 'never';
			$is_getter = !$is_setter || ($is_setter
					&& !is_null($return_type)
					&& $return_type !== 'void'
					&& $return_type !== 'never');

			if ($is_setter && $is_getter) {
				// BOTH
				$method_type = Property::TYPE_BOTH;
			} else if ($is_getter) {
				// GET
				$method_type = Property::TYPE_GET;
			} else if ($is_setter) {
				// SET
				$method_type = Property::TYPE_SET;
			}
		}

		return $method_type;
	}
}
