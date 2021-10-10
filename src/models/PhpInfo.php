<?php

namespace spaf\simputils\models;

use ArrayAccess;
use Exception;
use spaf\simputils\components\InternalMemoryCache;
use spaf\simputils\components\SimpleObject;
use spaf\simputils\generic\constants\PHPInfoConst as constants;
use spaf\simputils\helpers\SystemHelper;
use spaf\simputils\PHP;
use spaf\simputils\traits\ArrayAccessReadOnlyTrait;
use function is_string;
use function json_decode;
use function json_encode;

/**
 * PHP Info class instance
 */
class PhpInfo extends SimpleObject implements ArrayAccess {
	use ArrayAccessReadOnlyTrait;

	/**
	 * @inheritdoc
	 */
	public static bool $to_string_format_json = true;

	// TODO Getters
	// TODO Finalize and clear functionality
	// TODO Finish documentation and phpcs and test coverage
	// TODO Refactor testing
	// TODO Implement representation as a string

	protected ?array $storage = null;

	private static array $replace_php_info_reg_exp_array = [];

	/**
	 *
	 */
	public function __construct() {
		$this->storage = static::compose();
	}

	/**
	 * Gets original phpinfo() as a string
	 *
	 * @param bool $use_fresh If to regenerate the PHP info data string
	 *
	 * @return string
	 * @internal This is internal functionality, suggested to avoid using it directly
	 *           in your projects.
	 * @see \phpinfo()
	 */
	public static function getOriginalPhpInfo(bool $use_fresh = false): string {
		if ($use_fresh || empty(InternalMemoryCache::$original_phpinfo_string)) {
			ob_start(); phpinfo(); InternalMemoryCache::$original_phpinfo_string = ob_get_clean();
		}

		return InternalMemoryCache::$original_phpinfo_string;
	}

	/**
	 * @param string               $key      Key
	 * @param callable|string|null $callback Callback
	 * @param array|null           $reg_exps Custom reg_exps array
	 *
	 * @return mixed
	 * @internal This functionality is yet internal. Don't use it unless it stops being internal.
	 */
	protected static function extractPhpInfoPiece(
		string $key,
		null|callable|string $callback = null,
		array $reg_exps = null
	): mixed {
		$reg_exps = $reg_exps ?? static::getPhpInfoRegExpArray();
		$phpinfo = static::getOriginalPhpInfo();
		if (is_string($callback)) {
			$callback = match ($callback) {
				'bool', 'boolean' => fn($v) => PHP::asBool($v ?? ''),
				'empty-null' => fn($v) => empty($v)?null:$v,
			};
		}
		$callback = $callback ?? fn($v) => $v;

		$tmp = [];
		preg_match($reg_exps[$key], $phpinfo, $tmp);
		return $callback($tmp['val']);
	}

	/**
	 * @return array
	 */
	protected static function compose(): array {
		$reg_exps = static::getPhpInfoRegExpArray();

		$data = [];

		$data[constants::KEY_PHP_VERSION] = PHP::phpVersion();
		$data[constants::KEY_INI_CONFIG] = ini_get_all(details: false);
		$data[constants::KEY_MAIN_INI_FILE] = php_ini_loaded_file();
		$data[constants::KEY_EXTRA_INI_FILES] = explode(
			',', preg_replace('/\n*/', '', php_ini_scanned_files())
		);
		$data[constants::KEY_STREAM_WRAPPERS] = stream_get_wrappers();
		$data[constants::KEY_STREAM_TRANSPORTS] = stream_get_transports();
		$data[constants::KEY_STREAM_FILTERS] = stream_get_filters();
		$data[constants::KEY_ZEND_VERSION] = new Version(zend_version(), 'Zend');
		$data[constants::KEY_XDEBUG_VERSION] = !empty($v = phpversion('xdebug'))
			?new Version($v, 'xdebug')
			:null; //@codeCoverageIgnore
		$data[constants::KEY_ENV_VARS] = getenv();
		$data[constants::KEY_SERVER_VAR] = $_SERVER;

		$loaded_extensions = get_loaded_extensions();
		$data[constants::KEY_EXTENSIONS] = [];

		foreach ($loaded_extensions as $ext) {
			$data[constants::KEY_EXTENSIONS][$ext] = new Version(phpversion($ext), $ext);
		}

		/** @noinspection PhpComposerExtensionStubsInspection */
		$data[constants::KEY_OPCACHE] = extension_loaded('Zend OPcache')
			?opcache_get_status()
			:null;
		$data[constants::KEY_SYSTEM_OS] = SystemHelper::os();
		$data[constants::KEY_KERNEL_NAME] = SystemHelper::kernelName();
		$data[constants::KEY_SYSTEM_NAME] = SystemHelper::systemName();
		$data[constants::KEY_KERNEL_RELEASE] = SystemHelper::kernelRelease();
		$data[constants::KEY_KERNEL_VERSION] = SystemHelper::kernelVersion();
		$data[constants::KEY_CPU_ARCHITECTURE] = SystemHelper::cpuArchitecture();
		$data[constants::KEY_SAPI_NAME] = SystemHelper::serverApi();

		$keys_list_fn_callbacks = [
			constants::KEY_IS_THREAD_SAFE => 'bool',
			constants::KEY_IS_DEBUG_BUILD => 'bool',
			constants::KEY_ZEND_SIGNAL_HANDLING => 'bool',
			constants::KEY_ZEND_MEMORY_MANAGER => 'bool',
			constants::KEY_VIRTUAL_DIRECTORY_SUPPORT => 'bool',

			constants::KEY_ZEND_EXTENSION_BUILD => 'empty-null',
			constants::KEY_PHP_EXTENSION_BUILD => 'empty-null',

			constants::KEY_ZEND_MULTIBYTE_SUPPORT
				=> fn($v) => !empty($m['val']) && $m['val'] == 'provided by mbstring',

			constants::KEY_PHP_API_VERSION
				=> fn($v) => !empty($v)
					?new Version($v, 'PHP API')
					:null, // @codeCoverageIgnore

			constants::KEY_PHP_EXTENSION_VERSION
				=> fn($v) => !empty($v)
					?new Version($v, 'PHP Extension')
					:null, // @codeCoverageIgnore

			constants::KEY_ZEND_EXTENSION_VERSION
				=> fn($v) => !empty($v)
					?new Version($v, 'Zend Extension')
					:null, // @codeCoverageIgnore
		];

		foreach ($keys_list_fn_callbacks as $key => $callback) {
			$data[$key] = static::extractPhpInfoPiece($key, $callback, $reg_exps);
			// $reg_exps array is provided for purpose of optimization
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public static function listOfRegExpKeys(): array {
		return [
			constants::KEY_IS_THREAD_SAFE, constants::KEY_IS_DEBUG_BUILD,
			constants::KEY_PHP_API_VERSION, constants::KEY_ZEND_SIGNAL_HANDLING,
			constants::KEY_ZEND_MEMORY_MANAGER, constants::KEY_ZEND_MULTIBYTE_SUPPORT,
			constants::KEY_VIRTUAL_DIRECTORY_SUPPORT, constants::KEY_PHP_EXTENSION_VERSION,
			constants::KEY_ZEND_EXTENSION_VERSION, constants::KEY_ZEND_EXTENSION_BUILD,
			constants::KEY_PHP_EXTENSION_BUILD,
		];
	}

	/**
	 * @param string  $key Key
	 * @param ?string $val Value
	 *
	 * @todo relocate to Lib engine Settings
	 * @return void
	 * @throws \Exception If a key is not a valid name
	 */
	public static function replacePhpInfoRegExp(string $key, ?string $val) {
		$keys_list = static::listOfRegExpKeys();
		if (!in_array($key, $keys_list))
			throw new Exception('Key '.$key.' is not a valid name');

		static::$replace_php_info_reg_exp_array[$key] = $val;
	}

	/**
	 * @return array
	 */
	public static function getPhpInfoRegExpArray(): array {
		$yes_no_set = static::getYesNoArrayAsRegExpChoices();
		$ref = &static::$replace_php_info_reg_exp_array;
		return [
			$k = constants::KEY_IS_THREAD_SAFE
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Thread Safety => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_IS_DEBUG_BUILD
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Debug Build => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_ZEND_SIGNAL_HANDLING
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Zend Signal Handling => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_ZEND_MEMORY_MANAGER
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Zend Memory Manager => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_ZEND_MULTIBYTE_SUPPORT
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Zend Multibyte Support => (?P<val>provided by mbstring)/i'),

			$k = constants::KEY_VIRTUAL_DIRECTORY_SUPPORT
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Virtual Directory Support => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_PHP_API_VERSION
				=> !empty($ref[$k])
					?$ref[$k]
					:('/PHP API => (?P<val>\d+)/i'),

			$k = constants::KEY_PHP_EXTENSION_VERSION
				=> !empty($ref[$k])
					?$ref[$k]
					:('/PHP Extension => (?P<val>\d+)/i'),

			$k = constants::KEY_ZEND_EXTENSION_VERSION
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Zend Extension => (?P<val>\d+)/i'),

			$k = constants::KEY_ZEND_EXTENSION_BUILD
				=> !empty($ref[$k])
					?$ref[$k]
					:('/Zend Extension Build => (?P<val>.*)/i'),

			$k = constants::KEY_PHP_EXTENSION_BUILD
				=> !empty($ref[$k])
					?$ref[$k]
					:('/PHP Extension Build => (?P<val>.*)/i'),
		];
	}

	/**
	 * @return string
	 */
	public static function getYesNoArrayAsRegExpChoices(): string {
		return implode('|', array_merge(PHP::$array_yes, PHP::$array_no));
	}

	/**
	 * Offset exists
	 *
	 * @param mixed $offset Offset
	 *
	 * @return bool
	 */
	public function offsetExists(mixed $offset): bool {
		return isset($this->storage[$offset]);
	}

	/**
	 * Getting a value by offset
	 *
	 * @param mixed $offset Offset
	 *
	 * @return mixed
	 */
	public function offsetGet(mixed $offset): mixed {
		return $this->storage[$offset];
	}

	/**
	 * @inheritdoc
	 */
	public function toArray(bool $with_class = false): array {
		$res = json_decode(json_encode($this->storage), true);
		if ($with_class)
			$res[PHP::$serialized_class_key_name] = static::class;
		return $res;
	}
}
