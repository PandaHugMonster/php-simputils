<?php


namespace spaf\simputils\helpers;



use spaf\simputils\models\Version;

/**
 *
 */
class SystemHelper {

	public static function php_info(): array {
		ob_start(); phpinfo(); $phpinfo = ob_get_clean();
		//

		$php_version = static::php_version();
		$ini_config = ini_get_all(details: false);
		$main_ini_file = php_ini_loaded_file();
		$extra_ini_files = explode(',', preg_replace('/\n*/', '', php_ini_scanned_files()));
		$stream_wrappers = stream_get_wrappers();
		$stream_transports = stream_get_transports();
		$stream_filters = stream_get_filters();
		$zend_version = new Version(zend_version(), 'Zend');
		$xdebug_version = !empty($v = phpversion('xdebug'))?new Version($v, 'xdebug'):null;
		$env_vars = getenv();
		$server_var = $_SERVER;
		$loaded_extensions = get_loaded_extensions();
		$loaded_extensions_versions = [];
		foreach ($loaded_extensions as $ext) {
			$loaded_extensions_versions[$ext] = new Version(phpversion($ext), $ext);
		}
		/** @noinspection PhpComposerExtensionStubsInspection */
		$opcache = extension_loaded('Zend OPcache')?opcache_get_status():null;
		$system_os = static::os();
		$kernel_name = static::kernel_name();
		$system_name = static::system_name();
		$kernel_release = static::kernel_release();
		$kernel_version = static::kernel_version();
		$cpu_architecture = static::cpu_architecture();
		$sapi_name = static::server_api();
		$yes_no_set = implode('|', array_merge(static::$array_yes, static::$array_no));
		$m = [];
		preg_match('/Thread Safety => (?P<val>'.$yes_no_set.')/i', $phpinfo, $m);
		$is_thread_safe = static::_covert_value_bool($m['val']??'');

		$m = [];
		preg_match('/Debug Build => (?P<val>'.$yes_no_set.')/i', $phpinfo, $m);
		$is_debug_build = static::_covert_value_bool($m['val']??'');

		$m = [];
		preg_match('/Zend Signal Handling => (?P<val>'.$yes_no_set.')/i', $phpinfo, $m);
		$zend_signal_handling = static::_covert_value_bool($m['val']??'');

		$m = [];
		preg_match('/Zend Memory Manager => (?P<val>'.$yes_no_set.')/i', $phpinfo, $m);
		$zend_memory_manager = static::_covert_value_bool($m['val']??'');

		$m = [];
		preg_match('/Zend Multibyte Support => (?P<val>provided by mbstring)/i', $phpinfo, $m);
		$zend_multibyte_support = !empty($m['val']) && $m['val'] == 'provided by mbstring';

		$m = [];
		preg_match('/Virtual Directory Support => (?P<val>'.$yes_no_set.')/i', $phpinfo, $m);
		$virtual_directory_support = static::_covert_value_bool($m['val']??'');

		$m = [];
		preg_match('/PHP API => (?P<val>\d+)/i', $phpinfo, $m);
		$php_api_version = !empty($m['val'])?new Version($m['val'], 'PHP API'):null;

		$m = [];
		preg_match('/PHP Extension => (?P<val>\d+)/i', $phpinfo, $m);
		$php_extension_version = !empty($m['val'])?new Version($m['val'], 'PHP Extension'):null;

		$m = [];
		preg_match('/Zend Extension => (?P<val>\d+)/i', $phpinfo, $m);
		$zend_extension_version = !empty($m['val'])?new Version($m['val'], 'Zend Extension'):null;

		$m = [];
		preg_match('/Zend Extension Build => (?P<val>.*)/i', $phpinfo, $m);
		$zend_extension_build = !empty($m['val'])?$m['val']:null;

		$m = [];
		preg_match('/PHP Extension Build => (?P<val>.*)/i', $phpinfo, $m);
		$php_extension_build = !empty($m['val'])?$m['val']:null;

		$data = [
			'php_version' => $php_version,
			'ini_config' => $ini_config,
			'main_ini_file' => $main_ini_file,
			'extra_ini_files' => $extra_ini_files,
			'stream_wrappers' => $stream_wrappers,
			'stream_transports' => $stream_transports,
			'stream_filters' => $stream_filters,
			'zend_version' => $zend_version,
			'xdebug_version' => $xdebug_version,
			'env_vars' => $env_vars,
			'server_var' => $server_var,
			'loaded_extensions' => $loaded_extensions,
			'extensions' => $loaded_extensions_versions,
			'opcache' => $opcache,
			'system_os' => $system_os,
			'kernel_name' => $kernel_name,
			'system_name' => $system_name,
			'kernel_release' => $kernel_release,
			'kernel_version' => $kernel_version,
			'cpu_architecture' => $cpu_architecture,
			'sapi_name' => $sapi_name,
			'is_thread_safe' => $is_thread_safe,
			'is_debug_build' => $is_debug_build,
			'zend_signal_handling' => $zend_signal_handling,
			'zend_memory_manager' => $zend_memory_manager,
			'zend_multibyte_support' => $zend_multibyte_support,
			'virtual_directory_support' => $virtual_directory_support,
			'php_api_version' => $php_api_version,
			'php_extension_version' => $php_extension_version,
			'zend_extension_version' => $zend_extension_version,
			'zend_extension_build' => $zend_extension_build,
			'php_extension_build' => $php_extension_build,
		];

		return $data;
	}

	public static array $array_yes = ['enabled', 'yes', 't', 'true', 'y', '\+', '1'];
	public static array $array_no = ['disabled', 'no', 'f', 'false', 'n', '-', '0'];

	private static function _covert_value_bool($val): ?bool {
		return isset($val) && in_array($val, static::$array_yes);
	}

	public static function php_version(): Version|string {
		return new Version(phpversion());
	}

	public static function os(): string {
		return PHP_OS_FAMILY?:PHP_OS;
	}

	public static function system_name(): string {
		return static::uname('n');
	}

	public static function kernel_name(): string {
		return static::uname('s');
	}

	public static function kernel_release(): string {
		return static::uname('r');
	}

	public static function kernel_version(): string {
		return static::uname('v');
	}

	public static function uname($type = 'a'): string {
		return php_uname($type);
	}

	public static function cpu_architecture(): string {
		return static::uname('m');
	}

	public static function server_api(): string {
		return PHP_SAPI;
	}
}