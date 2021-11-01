<?php

namespace spaf\simputils\traits\dsf;

use Closure;
use spaf\simputils\generic\constants\ConstPHPInfo;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;

/**
 * Separated trait of only data versioned methods from DSF
 *
 * Represents history of methods to get fingerprint-data for each version
 *
 * @see \spaf\simputils\components\SystemFingerprint
 * @see \spaf\simputils\traits\DefaultSystemFingerprintTrait
 */
trait DsfVersionsMethodsTrait {

	/**
	 *
	 * Example item of match:
	 * ```php
	 *  match(true) {
	 *      // The newest conditions
	 *
	 *      // '1.0.0' < $version <= '2.0.0
	 *      $version->gt($z = '1.0.0') && $version->lte($z = '2.0.0')
	 *          => Closure::fromCallable([$this, static::autoPrepareMethodName($z)]),
	 *
	 *      // Previous conditions ...
	 *  }
	 * ```
	 *
	 * NOTE Do not forget to adjust this method when adding new versions fingerprints
	 *
	 * @param Version $version Expected version
	 * @return callable
	 */
	private function versionApplicableMethodChoose(Version $version): callable {
		return match (true) {
			// ... new conditions should be added on the top ...

			// The default condition
			default
			=> Closure::fromCallable([$this, static::autoPrepareMethodName('1.0.0')])
		};
	}

	//// Newer versions functionality must be add on top of this block


	// @codingStandardsIgnoreStart

	private function version_1_0_0(): array {
		$phpi = PHP::info();
		$non_strict_array = [
			ConstPHPInfo::KEY_PHP_VERSION, ConstPHPInfo::KEY_ZEND_VERSION,
			ConstPHPInfo::KEY_OPCACHE, ConstPHPInfo::KEY_SYSTEM_OS,
			ConstPHPInfo::KEY_KERNEL_RELEASE, ConstPHPInfo::KEY_CPU_ARCHITECTURE,
			ConstPHPInfo::KEY_IS_THREAD_SAFE, ConstPHPInfo::KEY_IS_DEBUG_BUILD,
			ConstPHPInfo::KEY_ZEND_SIGNAL_HANDLING, ConstPHPInfo::KEY_ZEND_MEMORY_MANAGER,
			ConstPHPInfo::KEY_PHP_API_VERSION, ConstPHPInfo::KEY_PHP_EXTENSION_VERSION,
			ConstPHPInfo::KEY_ZEND_EXTENSION_VERSION,
		];
		$processing_array = &$non_strict_array;

		// copying
		$strict_array = array_merge($non_strict_array, [
			ConstPHPInfo::KEY_MAIN_INI_FILE,
			ConstPHPInfo::KEY_EXTRA_INI_FILES,
			ConstPHPInfo::KEY_INI_CONFIG,
			ConstPHPInfo::KEY_STREAM_WRAPPERS,
			ConstPHPInfo::KEY_STREAM_TRANSPORTS,
			ConstPHPInfo::KEY_STREAM_FILTERS,
			ConstPHPInfo::KEY_EXTENSIONS,
		]);

		$res = [];
		if ($this->strictness < 0) {
			$processing_array = &$strict_array;
		}

		foreach ($processing_array as $val) {
			$res[$val] = $phpi[$val];
		}

		return $res;
	}

	// @codingStandardsIgnoreEnd
}
