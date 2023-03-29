<?php

namespace spaf\simputils\traits;

use spaf\simputils\components\RenderedWrapper;
use spaf\simputils\models\Box;
use function spaf\simputils\basic\bx;
use function str_replace;

/**
 * Static renderer trait (for static helpers)
 */
trait BaseHtmlTrait {
	use StaticRendererTrait;

	/**
	 * Really simple html-tags generator
	 *
	 * @param string    $tag   Tag name
	 * @param ?string   $value content value
	 * @param array|Box $attrs HTML Attributes for the tag
	 *
	 * @return string|RenderedWrapper
	 */
	static function tag(
		string $tag,
		string $value = null,
		array|Box $attrs = []
	): string|RenderedWrapper {
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
