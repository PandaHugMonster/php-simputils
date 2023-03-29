<?php

namespace spaf\simputils\traits;

use Exception;
use spaf\simputils\attributes\Renderer;
use spaf\simputils\Attrs;
use spaf\simputils\components\RenderedWrapper;
use spaf\simputils\PHP;
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
		$methods = Attrs::collectMethods(static::class, Renderer::class);
		foreach ($methods as $method) {
			$res = $method(...$params);
			if ($res instanceof RenderedWrapper) {
				return $res;
			} else if (!is_null($res)) {
				throw new Exception(
					'Renderers must return either RenderedWrapper object, or null'
				);
			}
		}
		$res = '';
		foreach ($params as $param) {
			$res .= "{$param}";
		}

		return $res;
	}

}
