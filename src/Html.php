<?php
/** @noinspection PhpMissingParamTypeInspection */

namespace spaf\simputils;

use spaf\simputils\attributes\Renderer;
use spaf\simputils\components\RenderedWrapper;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\traits\BaseHtmlTrait;
use spaf\simputils\traits\StaticRendererTrait;

/**
 * HTML Helper
 *
 * This helper contains just a minimum of functionality for HTML generation.
 *
 * Why this helper includes only a few shortcuts for tags?!
 * - Because the main purpose of this helper is to provide basic functionality,
 * while persisting the minimal size.
 */
class Html {
	use StaticRendererTrait;
	use BaseHtmlTrait;

	/**
	 * DIV tag
	 *
	 * @param string    $content
	 * @param Box|array $attrs
	 *
	 * @return RenderedWrapper|string
	 */
	static function div(string $content, Box|array $attrs = []) {
		return static::tag('div', $content, $attrs);
	}

	/**
	 * SPAN tag
	 *
	 * @param string    $content
	 * @param Box|array $attrs
	 *
	 * @return RenderedWrapper|string
	 */
	static function span(string $content, Box|array $attrs = []) {
		return static::tag('span', $content, $attrs);
	}

	/**
	 * TIME tag
	 *
	 * @param DateTime $dt
	 * @param array    $attrs
	 *
	 * @return RenderedWrapper|string
	 */
	#[Renderer]
	static function dt(DateTime $dt, $attrs = []) {
		$attrs = PHP::box($attrs);
		$attrs['datetime'] = $dt->format('c');
		return static::tag('time', "{$dt}", $attrs);
	}
}
