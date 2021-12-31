<?php

namespace spaf\simputils\special;

use Exception;
use spaf\simputils\generic\BasicInitConfig;

/**
 * It stores registry of all the registered InitConfigs
 *
 */
class CodeBlocksCacheIndex {

	private static $index = [];

	public static function registerInitBlock(BasicInitConfig $config): ?bool {
		$name = $config->name ?? 'app';
		if (static::hasInitBlock($name)) {
			throw new Exception(
				'Code block can be registered just ones with a unique name. '.
				"Name \"$config->name\" is not unique. Config: {$config}"
			);
			// return false;
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
}
