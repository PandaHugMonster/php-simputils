<?php

namespace spaf\simputils\traits;

use ReflectionMethod;
use spaf\simputils\attributes\Renderer;
use spaf\simputils\Attrs;
use spaf\simputils\components\RenderedWrapper;
use spaf\simputils\PHP;
use TypeError;
use function is_null;

/**
 * Static renderer trait (for static helpers)
 */
trait StaticRendererTrait {

	/**
	 * Renders anything based on "Renderer" PHP Attribute
	 *
	 *
	 * @param mixed ...$params
	 *
	 * @return string
	 * @throws \Exception
	 * @see Renderer
	 */
	static function render(mixed ...$params): string {
		$params = PHP::box($params);
		$method_reflections = Attrs::collectMethodReflections(static::class, Renderer::class);
		foreach ($method_reflections as $method_reflection) {
			/** @var ReflectionMethod $method_reflection */

			$instance = $method_reflection->getDeclaringClass();
			$method_reflection->setAccessible(true);

			try {
				$res = $method_reflection->invoke($instance, ...$params);
			}
			catch (TypeError) {
				$res = null;
			}

			if ($res instanceof RenderedWrapper) {
				if (!$res->is_disabled) {
					return $res;
				}
			} else if (!is_null($res)) {
				throw new TypeError(//@codeCoverageIgnore
					'Renderers must return either RenderedWrapper object, or null'
				);//@codeCoverageIgnore
			}

			$method_reflection->setAccessible(false);
		}
		$res = '';
		foreach ($params as $param) {
			$res .= "{$param}";
		}

		return $res;
	}

	#[Renderer]
	static private function defaultRenderer($arg = null): RenderedWrapper {
		return new RenderedWrapper($arg);
	}
}
