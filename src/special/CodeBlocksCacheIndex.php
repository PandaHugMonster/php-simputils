<?php

namespace spaf\simputils\special;

use Closure;
use Exception;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\logger\Logger;
use spaf\simputils\models\BigNumber;
use spaf\simputils\models\Box;
use spaf\simputils\models\DataUnit;
use spaf\simputils\models\DateInterval;
use spaf\simputils\models\DatePeriod;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\DateTimeZone;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use spaf\simputils\models\GitRepo;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\L10n;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\StackFifo;
use spaf\simputils\models\StackLifo;
use spaf\simputils\models\StrObj;
use spaf\simputils\models\SystemFingerprint;
use spaf\simputils\models\Temperature;
use spaf\simputils\models\Version;

/**
 * It stores registry of all the registered InitConfigs
 *
 */
class CodeBlocksCacheIndex {

	private static $index = [];
	private static $redefinitions = [];

	public static function listDefaultRedefinableComponents(): Box {
		// NOTE Box here cannot be replaced with the dynamic ones.
		return new Box([
			InitConfig::REDEF_PD => InitConfig::REDEF_PD,
			InitConfig::REDEF_PR => InitConfig::REDEF_PR,

			InitConfig::REDEF_BOX => Box::class,
			InitConfig::REDEF_DATE_TIME => DateTime::class,
			InitConfig::REDEF_DATE_TIME_ZONE => DateTimeZone::class,
			InitConfig::REDEF_FILE => File::class,
			InitConfig::REDEF_DIR => Dir::class,
			InitConfig::REDEF_PHP_INFO => PhpInfo::class,
			InitConfig::REDEF_VERSION => Version::class,
			InitConfig::REDEF_LOGGER => Logger::class,

			InitConfig::REDEF_DATE_INTERVAL => DateInterval::class,
			InitConfig::REDEF_DATE_PERIOD => DatePeriod::class,
			InitConfig::REDEF_DATA_UNIT => DataUnit::class,
			InitConfig::REDEF_STACK_FIFO => StackFifo::class,
			InitConfig::REDEF_STACK_LIFO => StackLifo::class,
			InitConfig::REDEF_GIT_REPO => GitRepo::class,
			InitConfig::REDEF_BIG_NUMBER => BigNumber::class,
			InitConfig::REDEF_L10N => L10n::class,
			InitConfig::REDEF_TEMPERATURE => Temperature::class,
			InitConfig::REDEF_SYSTEM_FINGERPRINT => SystemFingerprint::class,
			InitConfig::REDEF_STR_OBJ => StrObj::class,
		]);
	}

	public static function registerInitBlock(BasicInitConfig $config): ?bool {
		$name = empty($config->name)
			?'app'
			:$config->name;
		if (static::hasInitBlock($name)) {
			throw new Exception(
				'Code block can be registered just once with a unique name. '.
				"Name \"{$config->name}\" is not unique. Config: {$config}"
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
		$name = empty($name)
			?'app'
			:$name;
		return static::$index[$name] ?? null;
	}

	public static function hasInitBlock($name): bool {
		return (bool) static::getInitBlock($name);
	}

	/**
	 * @param string $key
	 * @param \Closure|string|null $default
	 * @return \Closure|string|null
	 */
	public static function getRedefinition(
		string $key,
		null|Closure|string $default = null
	): null|Closure|string {
		if (empty(static::$redefinitions[$key])) {
			$list = static::listDefaultRedefinableComponents();
			if (!empty($list[$key])) {
				return $list[$key];
			}
			return $default; // @codeCoverageIgnore
		}
		return static::$redefinitions[$key];
	}
}
