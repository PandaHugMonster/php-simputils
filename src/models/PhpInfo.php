<?php

namespace spaf\simputils\models;

use Exception;
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\Boolean;
use spaf\simputils\generic\constants\ConstPHPInfo as constants;
use spaf\simputils\PHP;
use spaf\simputils\special\CommonMemoryCacheIndex;
use spaf\simputils\Str;
use spaf\simputils\System;
use spaf\simputils\traits\ArrayReadOnlyAccessTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function in_array;

/**
 * PHP Info class instance
 *
 * Represents the basic data from phpinfo() and similar/related places. Can be used as array/box,
 * or as an object. The data is read-only. In the most cases it's suggested to do not create it
 * manually as a new object, but rather using {@see PHP::info()} method, because it caches
 * the object, and you receive the same object with every call.
 *
 * FIX  Wrap all the `array`s into `Box`s
 *
 * @property-read Version $php_version
 * @property-read Version $simp_utils_version
 * @property-read string $simp_utils_license
 * @property-read Box $ini_config
 * @property-read string $main_ini_file
 * @property-read array $extra_ini_files
 * @property-read array $stream_wrappers
 * @property-read array $stream_transports
 * @property-read array $stream_filters
 * @property-read Version $zend_version
 * @property-read Version $xdebug_version
 * @property-read array $env_vars
 * @property-read array $server_var
 * @property-read \spaf\simputils\models\Box $extensions
 * @property-read boolean $opcache
 * @property-read string $system_os
 * @property-read string $kernel_name
 * @property-read string $system_name
 * @property-read string $kernel_release
 * @property-read string $kernel_version
 * @property-read string $cpu_architecture
 * @property-read string $sapi_name
 * @property-read boolean $is_thread_safe
 * @property-read boolean $is_debug_build
 * @property-read boolean $zend_signal_handling
 * @property-read boolean $zend_memory_manager
 * @property-read boolean $virtual_directory_support
 * @property-read string $zend_extension_build
 * @property-read string $php_extension_build
 * @property-read boolean $zend_multibyte_support
 * @property-read Version $php_api_version
 * @property-read Version $php_extension_version
 * @property-read Version $zend_extension_version
 *
 */
class PhpInfo extends Box {
	use ArrayReadOnlyAccessTrait;
	use RedefinableComponentTrait;

	public static bool $to_string_format_json = true;
	private static array $replace_php_info_reg_exp_array = [];

	protected int $iter_index = 0;

	/**
	 * Defining the properties and it's defaults
	 *
	 * @return array|\spaf\simputils\models\Box
	 * @see PropertyBatch
	 */
	#[PropertyBatch(storage: PropertyBatch::STORAGE_SELF)]
	private function defineFields(): array|Box {
		// IMP  Never use Property and PropertyBatch inside of PropertyBatch method.
		//      Only direct method calls!
		return $this->getKeys();
	}

	/**
	 * Constructor
	 *
	 * Has the initial values acquiring.
	 */
	public function __construct() {
		parent::__construct(static::compose());
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
		if ($use_fresh || empty(CommonMemoryCacheIndex::$original_phpinfo_string)) {
			ob_start(); phpinfo(); CommonMemoryCacheIndex::$original_phpinfo_string = ob_get_clean();
		}

		return CommonMemoryCacheIndex::$original_phpinfo_string;
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
		if (Str::is($callback)) {
			$callback = match ($callback) {
				'bool', 'boolean' => fn($v) => Boolean::from($v ?? ''),
				'empty-null' => fn($v) => empty($v)?null:$v,
			};
		}
		$callback = $callback ?? fn($v) => $v;

		$tmp = [];
		preg_match($reg_exps[$key], $phpinfo, $tmp);
		return $callback($tmp['val'] ?? null);
	}

	/**
	 * Acquiring values of PHP info and similar
	 *
	 * FIX  Review and implement $box_class for arrays
	 *
	 * @return array|\spaf\simputils\models\Box
	 */
	protected static function compose(): array|Box {
		$reg_exps = static::getPhpInfoRegExpArray();
		$version_class = PHP::redef(Version::class);
		$box_class = PHP::redef(Box::class);

		$data = new $box_class();

		$data[constants::KEY_PHP_VERSION] = PHP::version();
		$data[constants::KEY_SIMP_UTILS_VERSION] = PHP::simpUtilsVersion();
		$data[constants::KEY_SIMP_UTILS_LICENSE] = PHP::simpUtilsLicense();
		$data[constants::KEY_INI_CONFIG] = new $box_class(ini_get_all(details: false));
		$data[constants::KEY_MAIN_INI_FILE] = php_ini_loaded_file();
		$data[constants::KEY_EXTRA_INI_FILES] = new $box_class(explode(
			',', preg_replace('/\n*/', '', php_ini_scanned_files())
		));
		$data[constants::KEY_STREAM_WRAPPERS] = new $box_class(stream_get_wrappers());
		$data[constants::KEY_STREAM_TRANSPORTS] = new $box_class(stream_get_transports());
		$data[constants::KEY_STREAM_FILTERS] = new $box_class(stream_get_filters());
		$data[constants::KEY_ZEND_VERSION] = new $version_class(zend_version(), 'Zend');
		$data[constants::KEY_XDEBUG_VERSION] = !empty($v = phpversion('xdebug'))
			?new $version_class($v, 'xdebug')
			:null; //@codeCoverageIgnore
		// IMP  Due to weird and volatile $_ENV, for PhpInfo `getenv()` is used,
		//      what is thread-unsafe.
		$data[constants::KEY_ENV_VARS] = PHP::allEnvs();
		$data[constants::KEY_SERVER_VAR] = new $box_class($_SERVER);

		$loaded_extensions = new $box_class(get_loaded_extensions());
		$data[constants::KEY_EXTENSIONS] = new $box_class();

		foreach ($loaded_extensions as $ext) {
			$data[constants::KEY_EXTENSIONS][$ext] = new $version_class(phpversion($ext), $ext);
		}

		/** @noinspection PhpComposerExtensionStubsInspection */
		$data[constants::KEY_OPCACHE] = extension_loaded('Zend OPcache')
			?opcache_get_status()
			:null; //@codeCoverageIgnore
		$data[constants::KEY_SYSTEM_OS] = System::os();
		$data[constants::KEY_KERNEL_NAME] = System::kernelName();
		$data[constants::KEY_SYSTEM_NAME] = System::systemName();
		$data[constants::KEY_KERNEL_RELEASE] = System::kernelRelease();
		$data[constants::KEY_KERNEL_VERSION] = System::kernelVersion();
		$data[constants::KEY_CPU_ARCHITECTURE] = System::cpuArchitecture();
		$data[constants::KEY_SAPI_NAME] = System::serverApi();

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
					?new $version_class($v, 'PHP API')
					:null, // @codeCoverageIgnore

			constants::KEY_PHP_EXTENSION_VERSION
				=> fn($v) => !empty($v)
					?new $version_class($v, 'PHP Extension')
					:null, // @codeCoverageIgnore

			constants::KEY_ZEND_EXTENSION_VERSION
				=> fn($v) => !empty($v)
					?new $version_class($v, 'Zend Extension')
					:null, // @codeCoverageIgnore
		];

		foreach ($keys_list_fn_callbacks as $key => $callback) {
			$data[$key] = static::extractPhpInfoPiece($key, $callback, $reg_exps);
			// $reg_exps array is provided for purpose of optimization
		}

		return $data;
	}

	/**
	 * Reg-exp keys for values available only inside of {@see \phpinfo()} string
	 *
	 * @codeCoverageIgnore
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
	 * Fulfilling reg exp replaces
	 *
	 * @param string  $key Key
	 * @param ?string $val Value
	 *
	 * @codeCoverageIgnore
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
	 * Array of reg exp replaces
	 *
	 * @return array
	 */
	public static function getPhpInfoRegExpArray(): array {
		$yes_no_set = static::getYesNoArrayAsRegExpChoices();
		$ref = &static::$replace_php_info_reg_exp_array;
		return [
			$k = constants::KEY_IS_THREAD_SAFE
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Thread Safety => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_IS_DEBUG_BUILD
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Debug Build => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_ZEND_SIGNAL_HANDLING
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Zend Signal Handling => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_ZEND_MEMORY_MANAGER
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Zend Memory Manager => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_ZEND_MULTIBYTE_SUPPORT
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Zend Multibyte Support => (?P<val>provided by mbstring)/i'),

			$k = constants::KEY_VIRTUAL_DIRECTORY_SUPPORT
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Virtual Directory Support => (?P<val>'.$yes_no_set.')/i'),

			$k = constants::KEY_PHP_API_VERSION
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/PHP API => (?P<val>\d+)/i'),

			$k = constants::KEY_PHP_EXTENSION_VERSION
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/PHP Extension => (?P<val>\d+)/i'),

			$k = constants::KEY_ZEND_EXTENSION_VERSION
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Zend Extension => (?P<val>\d+)/i'),

			$k = constants::KEY_ZEND_EXTENSION_BUILD
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/Zend Extension Build => (?P<val>.*)/i'),

			$k = constants::KEY_PHP_EXTENSION_BUILD
				=> !empty($ref[$k])
					?$ref[$k] //@codeCoverageIgnore
					:('/PHP Extension Build => (?P<val>.*)/i'),
		];
	}

	/**
	 * Yes and No regexp variants
	 *
	 * @return string
	 */
	public static function getYesNoArrayAsRegExpChoices(): string {
		$yeses = Boolean::$array_yes;
		$noes = Boolean::$array_no;
		$both = array_merge($yeses, $noes);
		$to_escape = ['+', '-'];
		foreach ($both as $key => $item) {
			if (in_array($item, $to_escape)) {
				$both[$key] = '\\'.$item;
			}
		}
		return implode('|', $both);
	}

	/**
	 * Updating in real time `PhpInfo` env var value
	 *
	 * Strongly recommended to avoid using it directly.
	 * Use `PHP::envSet()` or `\spaf\simputils\basic\env_set()` instead!
	 *
	 * @param string $key Name of the var
	 * @param mixed  $val Value to set to the var
	 *
	 * @see \spaf\simputils\PHP::envSet()
	 *
	 */
	public function updateEnvVar(string $key, mixed $val): void {
		$this['env_vars'][$key] = $val;
	}

	public function hasExtension(string $name): bool {
		$name = Str::lower($name);
		foreach ($this->extensions->keys as $key) {
			if (Str::lower($key) === $name) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	public static function redefComponentName(): string {
		return InitConfig::REDEF_PHP_INFO;
	}
}
