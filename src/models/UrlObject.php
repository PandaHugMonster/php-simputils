<?php

namespace spaf\simputils\models;

use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\UrlCompatible;

class UrlObject extends SimpleObject {

	protected UrlCompatible|string|Box|array|null $_host = null;
	protected Box|array|string|null $_path = null;
	protected Box|array|null $_params = null;
	protected string|null $_protocol = null;
	protected string|null $_processor = null;
	protected Box|array|null $_data = null;

	/**
	 *
	 * Some info:
	 *      * Host can be "condensed" string containing all arguments - should be parsed
	 *      * Host can be everything without protocol - should be parsed
	 *      * Host can be just portion of URL - should be parsed
	 *      * Path can be path portion (array, string) + params if assoc indexes
	 *      * Params can contain only "get" encoded arguments
	 *      * Protocol just a string
	 *      * Processor should not be explicitly specified
	 *      * Data provided to the processors to incorporate/use in URLs
	 *
	 * All the above should be incremental, and no info should be lost. So if the params
	 * can be in all 3 ($host, $path and $params) all of them have to be aggregated!
	 *
	 *
	 * @param UrlCompatible|string|Box|array|null $host
	 * @param \spaf\simputils\models\Box|array|string|null $path
	 * @param \spaf\simputils\models\Box|array|null $params
	 * @param string|null $protocol
	 * @param string|null $processor
	 * @param mixed ...$data
	 */
	function __construct(
		UrlCompatible|string|Box|array $host = null,
		Box|array|string $path = null,
		Box|array $params = null,
		string $protocol = null,
		string $processor = null,
		mixed ...$data,
	) {
		// FIX  Implement decision making between http and https (dev/prod as well)
		$protocol = 'http';

	}

	public function __toString(): string {
		$host = null;
		$protocol = $this->_protocol;
		$path = null;
		$params = null;
		$user = null;
		$token = null;

		if ($this->_host instanceof UrlCompatible) {
			$host = $this->_host->forUrl($protocol);
		}
	}
}
