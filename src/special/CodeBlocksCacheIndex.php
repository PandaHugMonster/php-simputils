<?php

namespace spaf\simputils\special;

use Closure;
use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\exceptions\InitConfigException;
use spaf\simputils\exceptions\InitConfigNonUniqueCodeBlock;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\models\BigNumber;
use spaf\simputils\models\Box;
use spaf\simputils\models\DataUnit;
use spaf\simputils\models\DateInterval;
use spaf\simputils\models\DatePeriod;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\DateTimeZone;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use spaf\simputils\models\IPv4;
use spaf\simputils\models\IPv4Range;
use spaf\simputils\models\L10n;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\Set;
use spaf\simputils\models\StackFifo;
use spaf\simputils\models\StackLifo;
use spaf\simputils\models\TimeDuration;
use spaf\simputils\models\UrlObject;
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
			AppInitConfig::REDEF_PD => AppInitConfig::REDEF_PD,
			AppInitConfig::REDEF_PR => AppInitConfig::REDEF_PR,

			AppInitConfig::REDEF_BOX => Box::class,
			AppInitConfig::REDEF_DATE_TIME => DateTime::class,
			AppInitConfig::REDEF_DATE_TIME_ZONE => DateTimeZone::class,
			AppInitConfig::REDEF_FILE => File::class,
			AppInitConfig::REDEF_DIR => Dir::class,
			AppInitConfig::REDEF_PHP_INFO => PhpInfo::class,
			AppInitConfig::REDEF_VERSION => Version::class,
			// AppInitConfig::REDEF_LOGGER => Logger::class,

			AppInitConfig::REDEF_DATE_INTERVAL => DateInterval::class,
			AppInitConfig::REDEF_DATE_PERIOD => DatePeriod::class,
			AppInitConfig::REDEF_TIME_DURATION => TimeDuration::class,
			AppInitConfig::REDEF_DATA_UNIT => DataUnit::class,
			AppInitConfig::REDEF_STACK_FIFO => StackFifo::class,
			AppInitConfig::REDEF_STACK_LIFO => StackLifo::class,
			// AppInitConfig::REDEF_GIT_REPO => GitRepo::class,
			AppInitConfig::REDEF_BIG_NUMBER => BigNumber::class,
			AppInitConfig::REDEF_L10N => L10n::class,
			// AppInitConfig::REDEF_TEMPERATURE => Temperature::class,
			// AppInitConfig::REDEF_SYSTEM_FINGERPRINT => SystemFingerprint::class,
			// AppInitConfig::REDEF_STR_OBJ => StrObj::class,
			AppInitConfig::REDEF_SET => Set::class,
			AppInitConfig::REDEF_IPV4_RANGE => IPv4Range::class,
			AppInitConfig::REDEF_IPV4 => IPv4::class,
			AppInitConfig::REDEF_URL => UrlObject::class,
		]);
	}

	public static function registerInitBlock(BasicInitConfig $config): ?bool {
		$name = empty($config->name)?'app':$config->name;

		if (static::hasInitBlock($name)) {
			throw new InitConfigNonUniqueCodeBlock(
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
						throw new InitConfigException('Init Config Redefinitions Problem');
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
		$name = empty($name)?'app':$name;
		return static::$index[$name] ?? null;
	}

	public static function hasInitBlock($name): bool {
		return (bool) static::getInitBlock($name);
	}

	/**
	 * @param string               $key     Redefinition key
	 * @param \Closure|string|null $default Fallback
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
