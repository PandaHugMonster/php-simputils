<?php

namespace spaf\simputils\special;

use Closure;
use Exception;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\models\InitConfig;
use function in_array;

/**
 * It stores registry of all the registered InitConfigs
 *
 */
class CodeBlocksCacheIndex {

	private static $index = [];
	private static $redefinitions = [];

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
			$list = [
				InitConfig::REDEF_PD,
				InitConfig::REDEF_BOX,
				InitConfig::REDEF_DATE_TIME,
				InitConfig::REDEF_FILE,
				InitConfig::REDEF_PHP_INFO,
				InitConfig::REDEF_VERSION,
				InitConfig::REDEF_LOGGER,
			];

			if (!empty($config->redefinitions)) {
				foreach ($config->redefinitions as $key => $redef) {
					if (!in_array($key, $list)) {
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
			return $default;
		}
		return static::$redefinitions[$key];
	}
}
