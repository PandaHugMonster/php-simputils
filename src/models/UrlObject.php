<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\NotImplementedYet;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\urls\processors\HttpSchemeProcessor;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\ForOutputsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function explode;
use function is_array;
use function is_null;
use function is_numeric;
use function is_string;
use function preg_match;
use function preg_replace;
use function spaf\simputils\basic\ic;
use function spaf\simputils\basic\pd;

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


	/**
	 * CS operation "prepend", will cause to add elements to the left of the group
	 */
	const CS_OP_PREPEND = 'prepend';

	/**
	 * CS operation "append", will cause to add elements to the right of the group
	 */
	const CS_OP_APPEND = 'append';

	/**
	 * CS operation "replace", will cause to replace all the elements of the group
	 */
	const CS_OP_REPLACE = 'replace';

	static string $default_processor = HttpSchemeProcessor::class;

	static array $processors = [
		'http' => HttpSchemeProcessor::class,
		'https' => HttpSchemeProcessor::class,
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
	 * @param string $operation
	 *
	 * @return $this
	 */
	#[Shortcut('$this->path')]
	function csPath(null|Box|array|string $path, string $operation = self::CS_OP_REPLACE): self {
		$path_new = static::preProcessPathData($path);
		$path_old = $this->path ?: PHP::box()->pathAlike();

		$res = match ($operation) {
			static::CS_OP_REPLACE => $path_new,
			static::CS_OP_PREPEND => static::addPathElements($path_new, $path_old),
			static::CS_OP_APPEND => static::addPathElements($path_old, $path_new),
		};

		$this->path = $res;
		return $this;
	}

	private static function addPathElements($to, $from) {
		foreach ($from as $k => $val) {
			if (is_numeric($k)) {
				$to[] = $val;
			} else {
				$to[$k] = $val;
			}
		}
		return $to;
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
	 * @param Box|array|null $params
	 * @param string $operation
	 *
	 * @return $this
	 */
	#[Shortcut('$this->params')]
	function csParams(null|Box|array $params, string $operation = self::CS_OP_REPLACE): self {
		$this->params = $this->preProcessParamsData($params);
		return $this;
	}

	/**
	 * @param UrlCompatible|string|Box|array|null $host
	 * @param Box|array|string|null $path
	 * @param Box|array|null $params
	 * @param string|null $protocol
	 * @param string|null $processor
	 * @param string|null $port
	 * @param string|null $user
	 * @param string|null $pass
	 * @param mixed ...$data
	 *
	 * @return $this
	 */
	function update(
		null|UrlCompatible|string|Box|array $host = null,
		null|Box|array|string $path = null,
		null|Box|array $params = null,
		?string $protocol = null,
		?string $processor = null,
		?string $port = null,
		?string $user = null,
		?string $pass = null,
		mixed ...$data,
	): static {
		// MARK Proceed here!

		return $this;
	}

	#[Property]
	protected ?string $_processor = null;

//	FIX After implementing multiple properties (and arrayed names)
	#[Property]
	#[Property('protocol')]
	protected ?string $_scheme = null;

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

	static function preProcessPathData($val) {
		$res = null;
		if ($val instanceof Box) {
			$val->pathAlike();
			$res = $val;
		} else if (is_array($val)) {
			$res = PHP::box($val)->pathAlike();
		} else if (is_string($val)) {
			$val = Str::removeStarting($val, '/');
			$res = PHP::box(explode('/', $val))->pathAlike();
		}
		return $res;
	}

	protected function preProcessParamsData($val) {
		$res = null;
		if ($val instanceof Box) {
			$val->paramsAlike();
			$res = $val;
		} else if (is_array($val)) {
			$res = PHP::box($val)->paramsAlike();
		} else if (is_string($val)) {
			// FIX  Proceed here with params data parsing
//			pd('FIX', $val);
			$proc = $this->_processor;
			/** @var HttpSchemeProcessor $proc */
			$r = $proc::parse($val, part: HttpSchemeProcessor::PART_PARAMS);
			pd('FIX RES', $r);
//			$val = Str::removeStarting($val, '/');
//			$res = PHP::box(explode('/', $val))->pathAlike();
		}
		return $res;
	}

	#[Property('path')]
	protected function setPathProperty($val) {
		$this->_path = static::preProcessPathData($val);
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
		null|Box|array|string $params = null,
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

		$this->_scheme = $protocol ?: $parsed->get('protocol');
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
			$pre_data = $pre_params = $pre = null;
			if (PHP::isArrayCompatible($path)) {
				$pre = PHP::box($path)->pathAlike();
			} else if (is_string($path)) {
				/** @var Box $_def_pre */
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

			if (Str::is($params)) {
				$params = $this->preProcessParamsData($params);
			}

			$pre_data = $pre = null;
			if (PHP::isArrayCompatible($params)) {
				$pre = PHP::box($params)->paramsAlike();
			} else if (is_string($path)) {
				/** @var Box $_def_pre */
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
