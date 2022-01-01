<?php


namespace spaf\simputils;



use spaf\simputils\generic\BasicSystemFingerprint;
use spaf\simputils\models\SystemFingerprint;
use spaf\simputils\models\Version;

/**
 *
 */
class System {

	/**
	 * @return string
	 */
	public static function os(): string {
		return PHP_OS_FAMILY?:PHP_OS;
	}

	/**
	 * @return string
	 */
	public static function systemName(): string {
		return static::uname('n');
	}

	/**
	 * @return string
	 */
	public static function kernelName(): string {
		return static::uname('s');
	}

	/**
	 * @return string
	 */
	public static function kernelRelease(): string {
		return static::uname('r');
	}

	/**
	 * @return string
	 */
	public static function kernelVersion(): string {
		return static::uname('v');
	}

	/**
	 * @param string $type Type of the uname to return (cli key basically)
	 *
	 * @return string
	 */
	public static function uname(string $type = 'a'): string {
		return php_uname($type);
	}

	/**
	 * @return string
	 */
	public static function cpuArchitecture(): string {
		return static::uname('m');
	}

	/**
	 * @return string
	 */
	public static function serverApi(): string {
		return PHP_SAPI;
	}

	public static function systemFingerprint(Version|string $version = null)
	: BasicSystemFingerprint|string {
		$version = $version ?? PHP::simpUtilsVersion();
		return new SystemFingerprint($version);
	}
}
