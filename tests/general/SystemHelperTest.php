<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\helpers\SystemHelper;

/**
 * @covers \spaf\simputils\helpers\SystemHelper
 * @uses \spaf\simputils\models\Version
 * @uses \spaf\simputils\Settings
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\versions\DefaultVersionParser
 */
class SystemHelperTest extends TestCase {

	public function testPhpInfo() {
		$php_info = SystemHelper::php_info();
		$this->assertIsArray($php_info, 'PHP info is an array');
		$this->assertNotEmpty($php_info, 'PHP info is not empty');

		$expected_keys = [
			'php_version', 'ini_config', 'main_ini_file', 'extra_ini_files', 'stream_wrappers',
			'stream_transports', 'stream_filters', 'zend_version', 'xdebug_version',
			'env_vars', 'server_var', 'loaded_extensions', 'extensions', 'opcache',
			'system_os', 'kernel_name', 'system_name', 'kernel_release', 'kernel_version',
			'cpu_architecture', 'sapi_name', 'is_thread_safe', 'is_debug_build',
			'zend_signal_handling', 'zend_memory_manager', 'zend_multibyte_support',
			'virtual_directory_support', 'php_api_version', 'php_extension_version',
			'zend_extension_version', 'zend_extension_build', 'php_extension_build',
		];
		foreach ($expected_keys as $key)
			$this->assertArrayHasKey($key, $php_info, 'Does have '.$key);

	}

}