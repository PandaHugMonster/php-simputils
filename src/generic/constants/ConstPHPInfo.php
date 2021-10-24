<?php

namespace spaf\simputils\generic\constants;

/**
 * PHP info constants
 *
 * @see \spaf\simputils\models\PhpInfo
 */
interface ConstPHPInfo {

	const KEY_IS_THREAD_SAFE = 'is_thread_safe';
	const KEY_IS_DEBUG_BUILD = 'is_debug_build';
	const KEY_ZEND_SIGNAL_HANDLING = 'zend_signal_handling';
	const KEY_ZEND_MEMORY_MANAGER = 'zend_memory_manager';
	const KEY_ZEND_MULTIBYTE_SUPPORT = 'zend_multibyte_support';
	const KEY_VIRTUAL_DIRECTORY_SUPPORT = 'virtual_directory_support';
	const KEY_PHP_API_VERSION = 'php_api_version';
	const KEY_PHP_EXTENSION_VERSION = 'php_extension_version';
	const KEY_ZEND_EXTENSION_VERSION = 'zend_extension_version';
	const KEY_ZEND_EXTENSION_BUILD = 'zend_extension_build';
	const KEY_PHP_EXTENSION_BUILD = 'php_extension_build';

	const KEY_PHP_VERSION = 'php_version';
	const KEY_INI_CONFIG = 'ini_config';
	const KEY_MAIN_INI_FILE = 'main_ini_file';
	const KEY_EXTRA_INI_FILES = 'extra_ini_files';
	const KEY_STREAM_WRAPPERS = 'stream_wrappers';
	const KEY_STREAM_TRANSPORTS = 'stream_transports';
	const KEY_STREAM_FILTERS = 'stream_filters';
	const KEY_ZEND_VERSION = 'zend_version';
	const KEY_XDEBUG_VERSION = 'xdebug_version';
	const KEY_ENV_VARS = 'env_vars';
	const KEY_SERVER_VAR = 'server_var';
	const KEY_EXTENSIONS = 'extensions';
	const KEY_OPCACHE = 'opcache';
	const KEY_SYSTEM_OS = 'system_os';
	const KEY_KERNEL_NAME = 'kernel_name';
	const KEY_SYSTEM_NAME = 'system_name';
	const KEY_KERNEL_RELEASE = 'kernel_release';
	const KEY_KERNEL_VERSION = 'kernel_version';
	const KEY_CPU_ARCHITECTURE = 'cpu_architecture';
	const KEY_SAPI_NAME = 'sapi_name';

}
