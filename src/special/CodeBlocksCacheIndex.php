<?php

namespace spaf\simputils\special;

use Closure;
use Exception;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\logger\Logger;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\File;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\Version;

/**
 * It stores registry of all the registered InitConfigs
 *
 */
class CodeBlocksCacheIndex {

	private static $index = [];
	private static $redefinitions = [];

	public static function listDefaultRedefinableComponents(): Box {
		return new Box([
			InitConfig::REDEF_PD => InitConfig::REDEF_PD,
			InitConfig::REDEF_BOX => Box::class,
			InitConfig::REDEF_DATE_TIME => DateTime::class,
			InitConfig::REDEF_FILE => File::class,
			InitConfig::REDEF_PHP_INFO => PhpInfo::class,
			InitConfig::REDEF_VERSION => Version::class,
			InitConfig::REDEF_LOGGER => Logger::class,
		]);
	}

	public static function registerInitBlock(BasicInitConfig $config): ?bool {
		$name = $config->name ?? 'app';
		if (static::hasInitBlock($name)) {
			throw new Exception(
				'Code block can be registered just ones with a unique name. '.
				"Name \"$config->name\" is not unique. Config: {$config}"
			);
			// return false;
		}

		if ($name === 'app') {
			$list = static::listDefaultRedefinableComponents();

			if (!empty($config->redefinitions)) {
				foreach ($config->redefinitions as $key => $redef) {
					if (empty($list[$key])) {
						throw new Exception('');
					}
					static::$redefinitions[$key] = $redef;
				}
			}
		}

		$config->name = $name;
		static::$index[$config->name] = $config;
		return true;
	}

	public static function getInitBlock($name): ?BasicInitConfig {
		return static::$index[$name ?? 'app'] ?? null;
	}

	public static function hasInitBlock($name): bool {
		return (bool) static::getInitBlock($name);
	}

	public static function getRedefinition(
		string $key,
		null|Closure|string $default = null
	): null|Closure|string {
		if (empty(static::$redefinitions[$key])) {
			$list = static::listDefaultRedefinableComponents();
			if (!empty($list[$key])) {
				return $list[$key];
			}
			return $default;
		}
		return static::$redefinitions[$key];
	}
}
