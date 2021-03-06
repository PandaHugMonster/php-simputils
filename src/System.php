<?php


namespace spaf\simputils;



use spaf\simputils\models\IPv4;
use function gethostbyname;
use function gethostname;

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

	static function localIp(): IPv4 {
		return PHP::ip(gethostbyname(gethostname()));
	}
}
