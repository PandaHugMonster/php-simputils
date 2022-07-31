<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Extract;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\ProtocolProcessorIsUndefined;
use spaf\simputils\generic\BasicProtocolProcessor;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\urls\processors\HttpProtocolProcessor;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function is_array;
use function is_string;
use function preg_match;
use function preg_replace;

/**
 * @property-read UrlCompatible|string|Box|array|null $orig Contains original value of the "host".
 *                                                          really useful in case of parsing of
 *                                                          an url.
 * TODO $host and $protocol might become writable as well in the future
 * @property-read string $host
 * @property string $path
 * @property Box|array|null $params
 * @property-read string $protocol Protocol is representative, the value is taken
 *                                 from the processor object
 * @property Box|array|null $data
 * @property BasicProtocolProcessor $processor
 * @property string $relative
 */
class UrlObject extends SimpleObject {
	use ForOutputsTrait;
	use RedefinableComponentTrait;

	static ?string $default_host = null;
	static ?string $default_protocol = 'https';

	static Box|array $processors = [
		'http' => HttpProtocolProcessor::class,
		'https' => HttpProtocolProcessor::class,
	];

	#[Property(type: 'get')]
	protected UrlCompatible|string|Box|array|null $_orig = null;

	#[Property(type: 'get')]
	protected UrlCompatible|string|null $_host = null;

	#[Property]
	protected Box|array|string|null $_path = null;

	#[Property]
	protected Box|array|null $_params = null;

	#[Property]
	protected Box|array|null $_data = null;

	#[Extract(false)]
	#[DebugHide]
	protected ?BasicProtocolProcessor $_processor = null;

	#[Property('protocol')]
	protected function getProtocol(): string {
		return $this->processor->protocol;
	}

	#[Property('processor')]
	protected function getProcessor(): BasicProtocolProcessor {
		return $this->_processor;
	}

	#[Property('processor')]
	protected function setProcessor(BasicProtocolProcessor $val) {
		$this->_processor = $val; // @codeCoverageIgnore
	}

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
	 * @param UrlCompatible|string|Box|array|null $host      Host
	 * @param Box|array|string|null               $path      Path
	 * @param Box|array|null                      $params    Params
	 * @param string|null                         $protocol  Protocol
	 * @param string|null                         $processor Processor object
	 * @param mixed                               ...$data   Additional data
	 */
	function __construct(
		UrlCompatible|string|Box|array $host = null,
		Box|array|string $path = null,
		Box|array $params = null,
		string $protocol = null,
		string $processor = null,
		mixed ...$data,
	) {
		$this->_host = null;
		$this->_path = PHP::box();
		$this->_params = PHP::box();
		$this->_data = PHP::box();
		$this->_orig = $host;

		$this->parseHost($host, $protocol, $processor);
		$this->addPath($path);
		$this->addParams($params);
		$this->addData($data);
		$this->_path->pathAlike();
	}

	protected function parseHost(
		UrlCompatible|string|Box|array $host = null,
		?string $protocol = null,
		?string $processor = null
	) {
		if (!empty($host)) {
			if (is_string($host)) {
				$m = [];
				/** @noinspection RegExpUnnecessaryNonCapturingGroup */
				preg_match('#^(?:([a-zA-Z0-9 ]+):)(.*)$#S', $host ?? '', $m);
				$host = !empty($m[2])?$m[2]:$host;

				if (!$protocol) {
					$protocol = !empty($m[1])
						?preg_replace('#\s+#S', '', $m[1])
						:null;
				}
			} else if (is_array($host) || $host instanceof Box) {
				$host = PHP::box($host);

				$this->_path->mergeFrom($host->only_numeric);
				$this->_params->mergeFrom($host->only_assoc);
				$host = null;
			}
		}

		$protocol = $protocol?:static::$default_protocol;
		$this->_processor = $processor ?: static::chooseProcessor($protocol);

		if (!empty($host)) {
			[
				$this->_host,
				$this->_path,
				$this->_params,
				$this->_data
			] = $this->_processor->parse($host, true);
		}
	}

	function addPath(Box|array|string|null $path) {
		if (!empty($path)) {
			$proc = $this->processor;

			if (is_string($path)) {
				[$_, $_path, $_params, $_data] = $proc->parse($path, false);
				if (!empty($_path)) {
					$this->_path->mergeFrom($_path);
				}
				if (!empty($_params)) {
					$this->_params->mergeFrom($_params);
				}
				if (!empty($_data)) {
					$this->_data->mergeFrom($_data);
				}
			} else {
				// NOTE Path can contain params as well. Difference is in indexes
				$path = PHP::box($path);

				$this->_path->mergeFrom($path->only_numeric);
				$this->_params->mergeFrom($path->only_assoc);
			}
		}

		// TODO Re-implement properly without creating a new object every single time
		$r = PHP::box()->pathAlike();
		foreach ($this->_path as $item) {
			$sub = preg_replace('#\s+#S', '', $item);
			if (!empty($sub)) {
				$r[] = $sub;
			}
		}
		$this->_path = $r;
	}

	function addParams(Box|array|null $params) {
		if (!empty($params)) {
			$this->_params->mergeFrom($params);
		}

		$r = PHP::box();
		foreach ($this->_params as $k => $v) {
			$sub = preg_replace('#\s+#S', '', $k);
			if (!empty($sub)) {
				$r[$sub] = $v;
			}
		}
		$this->_params = $r;
	}

	function addData(Box|array|null $data) {
		if (!empty($data)) {
			$this->_data->mergeFrom($data);
		}
	}

	protected static function chooseProcessor(string $protocol): BasicProtocolProcessor {
		$protocols = PHP::box(static::$processors);
		if (!$protocols->containsKey($protocol)) {
			throw new ProtocolProcessorIsUndefined(
				"No processor defined for this protocol: {$protocol}"
			);
		}

		$class = $protocols[$protocol];
		return new $class($protocol);
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		$host = $this->_host ?? static::$default_host ?? 'localhost';
		return $this->_processor->generateForSystem(
			$host, $this->_path, $this->_params, $this->_data
		);
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		$host = $this->_host ?? static::$default_host ?? 'localhost';
		return $this->_processor->generateForUser(
			$host, $this->_path, $this->_params, $this->_data
		);
	}

	#[Property('relative')]
	protected function getRelative(): string {
		$host = $this->_host ?? static::$default_host ?? 'localhost';
		return $this->_processor->generateRelative(
			$host, $this->_path, $this->_params, $this->_data
		);
	}

	function setFromStr(string $for_system): static {
		$this->__construct($for_system);
		return $this;
	}

	function ___serialize(): Box|array {
		return [
			'for_system' => Str::ing($this->for_system),
		];
	}

	protected function ___deserialize(array|Box $data): static {
		$this->setFromStr($data['for_system']);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public static function redefComponentName(): string {
		return InitConfig::REDEF_URL;
	}
}
