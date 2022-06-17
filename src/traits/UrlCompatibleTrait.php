<?php

namespace spaf\simputils\traits;

/**
 * @property-read string $for_url
 */
trait UrlCompatibleTrait {

	function forUrl($protocol): string {
		return "{$protocol}://{$this}";
	}
}
