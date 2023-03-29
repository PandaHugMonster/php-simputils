<?php

namespace spaf\simputils;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionObject;
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
		if (is_object($instance)) {
			$reflection = new ReflectionObject($instance);
		} else if (is_string($instance) && class_exists($instance)) {
			$reflection = new ReflectionClass($instance);
		} else {
			throw new TypeError(
				'$instance is not Object or Class String or Class does not exist'
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
				$res->append(Closure::fromCallable([$instance, $reflection_method->name]));
			}
		}
		return $res;
	}

}
