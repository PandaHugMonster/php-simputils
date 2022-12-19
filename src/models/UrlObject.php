<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\NotImplementedYet;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\urls\processors\HttpProtocolProcessor;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function explode;
use function is_array;
use function is_null;
use function is_string;
use function preg_match;
use function preg_replace;
use function spaf\simputils\basic\ic;

/**
 *
 * **Important**: `cs` prefixed methods here are "chaining setting methods" to be used
 * instead of properties of the same name. For changing multiple aspects of your URL properties
 * might be not the most comfortable solution.
 *
 * @property-read UrlCompatible|string|Box|array|null $orig Contains original value of the "host".
 *                                                          really useful in case of parsing of
 *                                                          an url.
 * @property ?string $processor
 * @property ?string $protocol
 * @property ?string $user
 * @property ?string $password
 * @property ?string $host
 * @property ?int $port
 * @property null|Box|string $path
 * @property null|Box|string $params
 * @property ?string $sharpy
 * @property ?Box $data
 *
 * @property string $relative
 */
class UrlObject extends SimpleObject {
	use ForOutputsTrait;
	use RedefinableComponentTrait;

	static string $default_processor = HttpProtocolProcessor::class;

	static array $processors = [
		'http' => HttpProtocolProcessor::class,
		'https' => HttpProtocolProcessor::class,
	];

	/**
	 * Chained Setting of Protocol
	 *
	 * CS - in this context stands for `Chained Setting`
	 *
	 * This naming is done for compatibility reasons, due to possibility of `set` prefixed
	 * methods might collide with other frameworks "getter/setter" functionality, and cause
	 * unexpected behaviour.
	 *
	 * This method can be used the same way as the property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param ?string $protocol
	 *
	 * @return $this
	 */
	#[Shortcut('$this->protocol')]
	function csProto(?string $protocol): self {
		$this->protocol = $protocol;
		return $this;
	}

	/**
	 * Chained Setting of Host
	 *
	 * CS - in this context stands for `Chained Setting`
	 *
	 * This naming is done for compatibility reasons, due to possibility of `set` prefixed
	 * methods might collide with other frameworks "getter/setter" functionality, and cause
	 * unexpected behaviour.
	 *
	 * This method can be used the same way as the property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param ?string $host
	 *
	 * @return $this
	 */
	#[Shortcut('$this->host')]
	function csHost(?string $host): self {
		$this->host = $host;
		return $this;
	}

	/**
	 * Chained Setting of Port
	 *
	 * CS - in this context stands for `Chained Setting`
	 *
	 * This naming is done for compatibility reasons, due to possibility of `set` prefixed
	 * methods might collide with other frameworks "getter/setter" functionality, and cause
	 * unexpected behaviour.
	 *
	 * This method can be used the same way as the property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param ?int $port
	 *
	 * @return $this
	 */
	#[Shortcut('$this->port')]
	function csPort(?int $port): self {
		$this->port = $port;
		return $this;
	}

	/**
	 * Chained Setting of User
	 *
	 * CS - in this context stands for `Chained Setting`
	 *
	 * This naming is done for compatibility reasons, due to possibility of `set` prefixed
	 * methods might collide with other frameworks "getter/setter" functionality, and cause
	 * unexpected behaviour.
	 *
	 * This method can be used the same way as the property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param ?string $user
	 *
	 * @return $this
	 */
	#[Shortcut('$this->user')]
	function csUser(?string $user): self {
		$this->user = $user;
		return $this;
	}

	/**
	 * Chained Setting of Password
	 *
	 * CS - in this context stands for `Chained Setting`
	 *
	 * This naming is done for compatibility reasons, due to possibility of `set` prefixed
	 * methods might collide with other frameworks "getter/setter" functionality, and cause
	 * unexpected behaviour.
	 *
	 * This method can be used the same way as the property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param ?string $password
	 *
	 * @return $this
	 */
	#[Shortcut('$this->password')]
	function csPass(?string $password): self {
		$this->password = $password;
		return $this;
	}

	/**
	 * Chained Setting of Sharpy
	 *
	 * CS - in this context stands for `Chained Setting`
	 *
	 * This naming is done for compatibility reasons, due to possibility of `set` prefixed
	 * methods might collide with other frameworks "getter/setter" functionality, and cause
	 * unexpected behaviour.
	 *
	 * This method can be used the same way as the property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param ?string $sharpy
	 *
	 * @return $this
	 */
	#[Shortcut('$this->sharpy')]
	function csSharpy(?string $sharpy): self {
		$this->sharpy = $sharpy;
		return $this;
	}

	/**
	 * Chained Setting of Path
	 *
	 * CS - in this context stands for `Chained Setting`
	 *
	 * This naming is done for compatibility reasons, due to possibility of `set` prefixed
	 * methods might collide with other frameworks "getter/setter" functionality, and cause
	 * unexpected behaviour.
	 *
	 * This method can be used the same way as the property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param null|Box|array|string $path
	 * @param bool $extend
	 *
	 * @return $this
	 */
	#[Shortcut('$this->sharpy')]
	function csPath(null|Box|array|string $path, $extend = true): self {
		$this->path = $path;
		return $this;
	}

	/**
	 * Chain set method for PArams
	 *
	 * This method can be used the same way as property,
	 * but allows chaining due to native functions/methods nature.
	 *
	 * @param \spaf\simputils\models\Box|array|null $params
	 * @param bool $extend Extend (by default) or Replace
	 *
	 * @return $this
	 */
	function setParams(null|Box|array $params, $extend = true): self {
		$this->params = $params;
		return $this;
	}

	#[Property]
	protected ?string $_processor = null;

	#[Property]
	protected ?string $_protocol = null;

	#[Property]
	protected ?string $_user = null;

	#[Property]
	#[DebugHide(false)]
	protected ?string $_password = null;

	#[Property]
	protected ?string $_host = null;

	#[Property]
	protected ?int $_port = null;

	#[Property(type: 'get')]
	protected null|Box|string $_path = null;

	#[Property]
	protected ?Box $_params = null;

	#[Property('path')]
	protected function setPathProperty($val) {
		if ($val instanceof Box) {
			$val->pathAlike();
			$this->_path = $val;
		} else if (is_array($val)) {
			$this->_path = PHP::box($val)->pathAlike();
		} else if (is_string($val)) {
			$val = Str::removeStarting($val, '/');
			$this->_path = PHP::box(explode('/', $val))->pathAlike();
		}
	}

	#[Property]
	#[DebugHide]
	protected ?Box $_data = null;

	#[Property(type: 'get')]
	#[DebugHide(false)]
	protected UrlCompatible|string|Box|array|null $_orig = null;

	#[Property('sharpy')]
	protected function getSharpy(): ?string {
		return $this->data['sharpy'] ?? null;
	}

	#[Property('sharpy')]
	protected function setSharpyProperty($val) {
		$this->data['sharpy'] = $val;
	}

	function isCurrent(
		$with_path = true,
		$with_params = true,
		$with_port = false,
		$with_host = false,
		$with_sharpy = false
	): bool {
		return $this->isSimilar(
			PHP::currentUrl(),
			$with_path, $with_params, $with_port,
			$with_host, $with_sharpy
		);
	}

	function isSimilar(
		?UrlObject $url,
		$with_path = true,
		$with_params = true,
		$with_port = false,
		$with_host = false,
		$with_sharpy = false
	): bool {
		if (is_null($url)) {
			return false;
		}
		$res = true;

		if ($with_path) {
			$res = $res && ($this->path == $url->path);
		}

		if ($with_params) {
			$res = $res && ($this->params == $url->params);
		}

		if ($with_port) {
			$res = $res && ($this->port === $url->port);
		}

		if ($with_host) {
			$res = $res && ($this->host === $url->host);
		}

		if ($with_sharpy) {
			$res = $res && ($this->sharpy === $url->sharpy);
		}

		return $res;
	}

	function __construct(
		null|UrlCompatible|string|Box|array $host = null,
		null|Box|array|string $path = null,
		null|Box|array $params = null,
		?string $protocol = null,
		?string $processor = null,
		?string $port = null,
		?string $user = null,
		?string $pass = null,
		mixed ...$data,
	) {

		$this->_processor = $processor ?: static::$default_processor;
		$this->_orig = $host;
		$this->_data = PHP::box();

		if (!$host) {
			$host = ic()?->default_host ?? '';
		}

		$parsed = $this->parseHost($host);

		$this->_protocol = $protocol ?: $parsed->get('protocol');
		unset($parsed['protocol']);

		$this->_user = $user ?: $parsed->get('user');
		unset($parsed['user']);

		$this->_password = $pass ?: $parsed->get('pass');
		unset($parsed['pass']);

		$this->_host = $parsed->get('host');
		unset($parsed['host']);

		$this->_port = $port ?: $parsed->get('port');
		unset($parsed['port']);

		$this->_params = PHP::box()->paramsAlike();
		$this->_path = PHP::box()->pathAlike();
		$p = $parsed->get('path');
		if ($p && $p->size > 0) {
//			$parsed_path = $this->_processor::parse($p, part: 'path');
			$this->_path = PHP::box($this->_path, $p)->pathAlike();
		}

		$p = $parsed->get('params');
		if ($p && $p->size > 0) {
//			$parsed_params = $this->_processor::parse($p, part: 'params');
			$this->_params = PHP::box($this->_params, $p)->paramsAlike();
		}

		if ($path) {
//			pd($path);
			$pre_data = $pre_params = $pre = null;
			if (PHP::isArrayCompatible($path)) {
				$pre = PHP::box($path)->pathAlike();
			} else if (is_string($path)) {
				/** @var \spaf\simputils\models\Box $_def_pre */
				$_def_pre = $this->_processor::parse($path, part: 'path');
				$ll = $_def_pre->get('path');
				if ($ll) {
					$pre = PHP::box($ll)->pathAlike();
				}
				$pre_params = PHP::box($_def_pre->get('params'))->paramsAlike();
				unset($_def_pre['path']);
				unset($_def_pre['params']);
				unset($_def_pre['protocol']);
				unset($_def_pre['user']);
				unset($_def_pre['pass']);
				unset($_def_pre['host']);
				unset($_def_pre['port']);
				$pre_data = PHP::box((array) $_def_pre);
			}
			if ($pre) {
				if (!$pre_params) {
					$pre_params = PHP::box()->paramsAlike();
				}
				$new_pre = PHP::box()->pathAlike();
				foreach ($pre as $k => $p_item) {
					if (!preg_match('#^[+-]?[\d]*$#', $k)) {
						$pre_params[$k] = $p_item;
					} else {
						if (Str::contains($p_item, '/')) {
							$p_item = preg_replace('#/{2,}#', '/', $p_item);
							foreach (explode('/', $p_item) as $sub_p_item) {
								$new_pre->append("{$sub_p_item}");
							}
						} else {
							$new_pre->append("{$p_item}");
						}
					}
				}
				$this->_path = PHP::box($this->_path, $new_pre);

				//$pre_params
			}
			if ($pre_params) {
				$this->_params = PHP::box($this->_params, $pre_params);
			}
			if ($pre_data) {
				$this->_data = PHP::box($this->_data, $pre_data);
			}
//			pd($this->params, $pre_params);
		}

//		$this->_path = $path ?: $parsed['path'];

		unset($parsed['path']);

		if ($params) {
			$pre_data = $pre = null;
			if (PHP::isArrayCompatible($params)) {
				$pre = PHP::box($params)->paramsAlike();
			} else if (is_string($path)) {
				/** @var \spaf\simputils\models\Box $_def_pre */
				$_def_pre = $this->_processor::parse($params, part: 'params');
				$pre = PHP::box($_def_pre->get('params'))->paramsAlike();
				unset($_def_pre['path']);
				unset($_def_pre['params']);
				unset($_def_pre['protocol']);
				unset($_def_pre['user']);
				unset($_def_pre['pass']);
				unset($_def_pre['host']);
				unset($_def_pre['port']);
				$pre_data = PHP::box((array) $_def_pre);
			}
			if ($pre) {
				$this->_params = PHP::box($this->_params, $pre);
			}
			if ($pre_data) {
				$this->_data = PHP::box($this->_data, $pre_data);
			}
		}

		if ($this->_params && $this->_params->containsKey('#')) {
			$this->_data['sharpy'] = $this->_params['#'];
			unset($this->_params['#']);
		}

		unset($parsed['params']);

		if (!empty($parsed)) {
			$this->_data = PHP::box($parsed, $this->_data);
		}
	}

	/** @noinspection PhpUndefinedMethodInspection */
	protected function parseHost($val): Box|null {
		$m = [];
		$types = PHP::box(static::$processors)->keys->pathAlike('|');

		preg_match('#^('.$types.'):(.*)$#S', $val, $m);
		$def_sup = $this->_processor::supportedProtocols();
		if ($m && !$def_sup->containsValue($m[1])) {
			// NOTE Supported types
			throw new NotImplementedYet('Is not yet implemented.');
		} else {
			// NOTE Unsupported types or default

			$protocol = $m[1] ?? null;
			$val = $m[2] ?? $val;

			return $this->_processor::parse($val, $protocol);
		}
	}

	#[DebugHide(false)]
	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->_processor::generateForSystem($this);
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		return $this->_processor::generateForUser($this);
	}

	#[Property('relative')]
	protected function getRelative(): string {
		return $this->_processor::generateRelative($this);
	}

//
//	static ?string $default_host = null;
//	static ?string $default_protocol = 'https';
//
//	static Box|array $processors = [
//		'http' => HttpProtocolProcessor::class,
//		'https' => HttpProtocolProcessor::class,
//	];
//
//	#[Property(type: 'get')]
//	protected UrlCompatible|string|Box|array|null $_orig = null;
//
//	#[Property(type: 'get')]
//	protected UrlCompatible|string|null $_host = null;
//
//	#[Property]
//	protected Box|array|string|null $_path = null;
//
//	#[Property]
//	protected Box|array|null $_params = null;
//
//	#[Property]
//	protected Box|array|null $_data = null;
//
//	#[Property('port')]
//	protected function getPort(): ?int {
//		return $this->_processor->getPort($this);
//	}
//
//	#[Property('port')]
//	protected function setPort(?int $val) {
//		$this->_processor->setPort($this, $val);
//	}
//
//	#[Extract(false)]
//	#[DebugHide]
//	protected ?BasicProtocolProcessor $_processor = null;
//
//	#[Property('protocol')]
//	protected function getProtocol(): string {
//		return $this->processor->protocol;
//	}
//
//	#[Property('processor')]
//	protected function getProcessor(): BasicProtocolProcessor {
//		return $this->_processor;
//	}
//
//	#[Property('processor')]
//	protected function setProcessor(BasicProtocolProcessor $val) {
//		$this->_processor = $val; // @codeCoverageIgnore
//	}
//
//	/**
//	 *
//	 * Some info:
//	 *      * Host can be "condensed" string containing all arguments - should be parsed
//	 *      * Host can be everything without protocol - should be parsed
//	 *      * Host can be just portion of URL - should be parsed
//	 *      * Path can be path portion (array, string) + params if assoc indexes
//	 *      * Params can contain only "get" encoded arguments
//	 *      * Protocol just a string
//	 *      * Processor should not be explicitly specified
//	 *      * Data provided to the processors to incorporate/use in URLs
//	 *
//	 * All the above should be incremental, and no info should be lost. So if the params
//	 * can be in all 3 ($host, $path and $params) all of them have to be aggregated!
//	 *
//	 *
//	 * @param UrlCompatible|string|Box|array|null $host      Host
//	 * @param Box|array|string|null               $path      Path
//	 * @param Box|array|null                      $params    Params
//	 * @param string|null                         $protocol  Protocol
//	 * @param string|null                         $processor Processor object
//	 * @param mixed                               ...$data   Additional data
//	 */
//	function __construct(
//		UrlCompatible|string|Box|array $host = null,
//		Box|array|string $path = null,
//		Box|array $params = null,
//		string $protocol = null,
//		string $processor = null,
//		mixed ...$data,
//	) {
//		$this->_host = null;
//		$this->_path = PHP::box();
//		$this->_params = PHP::box();
//		$this->_data = PHP::box();
//		$this->_orig = $host;
//
//		$this->parseHost($host, $protocol, $processor);
//		$this->addPath($path);
//		$this->addParams($params);
//		$this->addData($data);
//		$this->_path->pathAlike();
//	}
//
//	protected function parseHost(
//		UrlCompatible|string|Box|array $host = null,
//		?string $protocol = null,
//		?string $processor = null
//	) {
//		if (!empty($host)) {
//			if (is_string($host)) {
//				$m = [];
//				$orig_host = $host;
//				preg_match('#^([a-zA-Z]?[a-zA-Z0-9_-]*):(.*)$#S', $host, $m);
//				if ($m) {
//					$protocol = $protocol ?? $m[1] ?? null;
//					$host = $m[2] ?? null;
//				}
//
//				// Str::startsWith($host, '//') || (!Str::startsWith($host, 'http://') && !Str::startsWith($host, 'https://'))
//				$tt = bx(static::$processors);
//				if (!$tt->containsKey($protocol) || bx(['http', 'https'])->containsValue($protocol)) {
//					// NOTE We need to check whether it's a valid http url without protocol part,
//					//      or it's something else.
//					$processor_class = static::$processors['http'] ?? null;
//					if (empty($processor_class)) {
//						throw new Exception('HTTP Protocol does not have a proper Processor Class specified for it!');
//					}
//					/** @var HttpProtocolProcessor $http_processor */
//					$protocol = empty($protocol) && $processor_class::isValid($host, $protocol)?'http':$protocol;
//
//					[$protocol, $user, $pass, $host, $port] = $processor_class::preParsing($orig_host, $protocol);
//					$this->data['user'] = $user;
//					$this->data['pass'] = $pass;
//					$this->data['port'] = $port;
//				}
//
////				pd($protocol);
//				// FIX  Parse here!
//
//			} else if (is_array($host) || $host instanceof Box) {
//				$host = PHP::box($host);
//
//				$this->_path->mergeFrom($host->only_numeric);
//				$this->_params->mergeFrom($host->only_assoc);
//				$host = null;
//			}
//		}
//
//		$protocol = $protocol?:static::$default_protocol;
//		if (is_null($protocol)) {
//			$protocol = 'https';
//		}
//		$this->_processor = $processor ?: static::chooseProcessor($protocol);
//
//		if (!empty($host)) {
//			[
//				$this->_host,
//				$this->_path,
//				$this->_params,
//				$this->_data
//			] = $this->_processor->parse($host, true, $this->data);
//		}
//	}
//
//	function addPath(Box|array|string|null $path) {
//		if (!empty($path)) {
//			$proc = $this->processor;
//
//			if (is_string($path)) {
//				[$_, $_path, $_params, $_data] = $proc->parse($path, false);
//				if (!empty($_path)) {
//					$this->_path->mergeFrom($_path);
//				}
//				if (!empty($_params)) {
//					$this->_params->mergeFrom($_params);
//				}
//				if (!empty($_data)) {
//					$this->_data->mergeFrom($_data);
//				}
//			} else {
//				// NOTE Path can contain params as well. Difference is in indexes
//				$path = PHP::box($path);
//
//				$this->_path->mergeFrom($path->only_numeric);
//				$this->_params->mergeFrom($path->only_assoc);
//			}
//		}
//
//		// TODO Re-implement properly without creating a new object every single time
//		$r = PHP::box()->pathAlike();
//		foreach ($this->_path as $item) {
//			$sub = preg_replace('#\s+#S', '', $item);
//			if (!empty($sub)) {
//				$r[] = $sub;
//			}
//		}
//		$this->_path = $r;
//	}
//
//	function addParams(Box|array|null $params) {
//		if (!empty($params)) {
//			$this->_params->mergeFrom($params);
//		}
//
//		$r = PHP::box();
//		foreach ($this->_params as $k => $v) {
//			$sub = preg_replace('#\s+#S', '', $k);
//			if (!empty($sub)) {
//				$r[$sub] = $v;
//			}
//		}
//		$this->_params = $r;
//	}
//
//	function addData(Box|array|null $data) {
//		if (!empty($data)) {
//			$this->_data->mergeFrom($data);
//		}
//	}
//
//	protected static function chooseProcessor(string $protocol): BasicProtocolProcessor {
//		$protocols = PHP::box(static::$processors);
//		if (!$protocols->containsKey($protocol)) {
//			throw new ProtocolProcessorIsUndefined(
//				"No processor defined for this protocol: {$protocol}"
//			);
//		}
//
//		$class = $protocols[$protocol];
//		return new $class($protocol);
//	}
//
//	#[Property('for_system')]
//	protected function getForSystem(): string {
//		$host = $this->_host ?? static::$default_host ?? 'localhost';
//		return $this->_processor->generateForSystem(
//			$host, $this->_path, $this->_params, $this->_data
//		);
//	}
//
//	#[Property('for_user')]
//	protected function getForUser(): string {
//		$host = $this->_host ?? static::$default_host ?? 'localhost';
//		return $this->_processor->generateForUser(
//			$host, $this->_path, $this->_params, $this->_data
//		);
//	}
//
//	#[Property('relative')]
//	protected function getRelative(): string {
//		$host = $this->_host ?? static::$default_host ?? 'localhost';
//		return $this->_processor->generateRelative(
//			$host, $this->_path, $this->_params, $this->_data
//		);
//	}


	function setFromData($data): static {
		$this->__construct($data['for_system']);
		return $this;
	}

	function ___serialize(): Box|array {
		return [
			'for_system' => Str::ing($this->for_system),
		];
	}

	protected function ___deserialize(array|Box $data): static {
		$this->setFromData($data);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public static function redefComponentName(): string {
		return InitConfig::REDEF_URL;
	}
}
