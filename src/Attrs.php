<?php

namespace spaf\simputils;

use Attribute;
use Closure;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionObject;
use Reflector;
use spaf\simputils\models\Box;
use TypeError;
use function class_exists;
use function is_object;
use function is_string;

/**
 * PHP Attributes Helper
 */
class Attrs {

	/**
	 * @param mixed  $instance
	 * @param string $attr
	 *
	 * @return Box|array
	 * @throws Exception
	 */
	static function collectMethods(mixed $instance, string $attr): Box|array {
		$res = PHP::box();
		$reflections = static::collectMethodReflections($instance, $attr);

		foreach ($reflections as $reflection_method) {
			/** @var ReflectionMethod $reflection_method */
			$res->append(Closure::fromCallable([
				$reflection_method->getClosureCalledClass(),
				$reflection_method->name,
			]));
		}

		return $res;
	}

	static function collectMethodReflections(mixed $instance, string $attr): Box|array {
		if (is_object($instance)) {
			$reflection = new ReflectionObject($instance);
		} else if (is_string($instance) && class_exists($instance)) {
			$reflection = new ReflectionClass($instance);
		} else {
			throw new TypeError(
				'$instance is not Object or Class String or Class does not exist',
			);
		}
		if (!is_string($attr) || !class_exists($attr)) {
			throw new TypeError('$attr is not Class String or Class does not exist');
		}
		$methods = $reflection->getMethods();
		$res = PHP::box();
		foreach ($methods as $reflection_method) {
			$is = $reflection_method->getAttributes($attr);
			if ($is) {
				$res->append($reflection_method);
			}
		}

		return $res;
	}

	static function findSpells(
		string|object $instance,
		null|Box|array $attrs = null,
		$attr_target = Attribute::TARGET_ALL,
		?callable $callback = null,
	) {

		$attr_target = Attribute::TARGET_ALL | $attr_target;

		$r = new ReflectionClass($instance);
		$res = PHP::box();

		$attrs = PHP::box($attrs);

		if (Boolean::isBitFlagOn($attr_target, $t = Attribute::TARGET_CLASS)) {
			static::_processAttributes($res, $r, $t, $attrs, $callback);
		}

		if (Boolean::isBitFlagOn($attr_target, $t = Attribute::TARGET_PROPERTY)) {
			foreach ($r->getProperties() as $ref_item) {
				static::_processAttributes($res, $ref_item, $t, $attrs, $callback);
			}
		}

		if (Boolean::isBitFlagOn($attr_target, $t = Attribute::TARGET_METHOD)) {
			foreach ($r->getMethods() as $ref_item) {
				static::_processAttributes($res, $ref_item, $t, $attrs, $callback);
			}
		}

		if (Boolean::isBitFlagOn($attr_target, $t = Attribute::TARGET_CLASS_CONSTANT)) {
			foreach ($r->getConstants() as $key => $val) {
				$ref_item = new ReflectionClassConstant($instance, $key);
				static::_processAttributes($res, $ref_item, $t, $attrs, $callback);
			}
		}

		return $res;
	}

	static protected function _processAttributes(
		&$res,
		Reflector $ref_item,
		int $target,
		Box $attrs,
		?callable $callback,
	) {
		foreach ($ref_item->getAttributes() as $at) {
			/** @var ReflectionAttribute $at */
			if (!$attrs->size || $attrs->containsValue($at->getName())) {
				if (!$callback || $callback($ref_item, $at)) {
					$res->append([
						'target' => $target,
						'reflection' => $ref_item,
						'attribute' => $at->newInstance(),
					]);
				}
			}
		}
	}

}
