<?php

namespace spaf\simputils\traits;

use spaf\simputils\components\RenderedWrapper;
use function spaf\simputils\basic\bx;
use function str_replace;

/**
 * Static renderer trait (for static helpers)
 */
trait BaseHtmlTrait {
	use StaticRendererTrait;

	static function tag($tag, $value = null, $attrs = []): string|RenderedWrapper {
		$attrs = bx($attrs)->stretched(separator: ' ', value_wrap: function ($value) {
			$value = str_replace("\\\"", "\"", $value);
			$value = str_replace("\"", "\\\"", $value);
			return "\"{$value}\"";
		});

		$attrs = !empty("{$attrs}")
			?" {$attrs}"
			:"{$attrs}";

		$res = "<{$tag}{$attrs}>{$value}</{$tag}>";

		return new RenderedWrapper($res);
	}
}
