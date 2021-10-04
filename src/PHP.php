<?php
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpNeverTypedFunctionReturnViolationInspection */

namespace spaf\simputils;


use Exception;
use ReflectionClass;
use spaf\simputils\traits\MetaMagic;
use Throwable;
use function is_array;
use function is_resource;
use function json_decode;
use function json_encode;
use function json_last_error;
use function serialize;
use function unserialize;
use const JSON_ERROR_NONE;

/**
 * Special static PHP helper
 *
 * Contains fix of the PHP platform "issues" and "missing features", like really disappointing serialize() feature that does not
 * provide ability to use "json" as output.
 *
 * @package spaf\simputils
 */
class PHP {

	const SERIALIZATION_TYPE_JSON = 0;
	const SERIALIZATION_TYPE_PHP = 1;

	// TODO Maybe #class? Checkout compatibility with JavaScript and other techs and standards
	public static string $serialized_class_key_name = '_class';
	public static string|int $serialization_mechanism = self::SERIALIZATION_TYPE_JSON;

	/**
	 * Serialize any data
	 *
	 * @param mixed $data
	 *
	 * @return false|string
	 * @throws \Exception
	 */
	public static function serialize(mixed $data): false|string {
		if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_JSON) {
			if (static::classUsesTrait($data, MetaMagic::class)) {
				$res = $data::_metaMagic($data, '___serialize');
			}

			return json_encode($res);
		}
		if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_PHP) {
			if (is_resource($data))
				throw new Exception('Resources cannot be serialized through PHP default mechanisms');
			return \serialize($data);
		}

		return false;
	}

	/**
	 * Deserialize data serialized by {@see serialize()} method
	 *
	 * @param string|null $str
	 * @param null $class
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public static function deserialize(string|null $str, $class = null): mixed {
		if (empty($str))
			return null;

		if (empty($class))
			$class = static::determine_serialized_class($str);

		if (empty($class))
			// TODO Fix this exception to a more appropriate one
			throw new Exception('Cannot determine class for deserialization');

		if (static::classUsesTrait($class, MetaMagic::class)) {
			if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_JSON) {
				$dummy = (new ReflectionClass($class))->newInstanceWithoutConstructor();
				$data = json_decode($str, true);
				/** @noinspection PhpUndefinedMethodInspection */
				return $class::_metaMagic($dummy, '___deserialize', $data);
			}
		}
		if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_PHP) {
			return \unserialize($str);
		}

		return null;
	}

	private static function determine_serialized_class(string $str): ?string {
		$data = json_decode($str, true);
		// JSON parsing
		if (json_last_error() === JSON_ERROR_NONE) {
			if (is_array($data) && !empty($data[static::$serialized_class_key_name]))
				return $data[static::$serialized_class_key_name];
		} else {
			$res = unserialize($str);
			if (!$res instanceof Throwable) {
				return $res::class;
			}
		}

		return null;
	}

	public static function classUsesTrait(object|string $class_ref, $trait_ref): bool {
		foreach (class_parents($class_ref) as $cp) {
			$traits = class_uses($cp);
			if (!empty($traits)) {
				foreach ($traits as $trait) {
					if ($trait == $trait_ref)
						return true;
				}
			}
		}
		return false;
	}

	public static function isJsonString(string $json_or_not): bool {
		json_decode($json_or_not, true);
		if (json_last_error() === JSON_ERROR_NONE)
			return true;
		return false;
	}

	/**
	 * Please Die function
	 *
	 * Print out all the supplied params, and then die/exit the runtime.
	 * Basically, could be considered as a shortcut of sequence of "print_r + die"
	 *
	 * Besides that, the functionality can be redefined. For example if you want
	 * use your own implementation, you can just redefine it on a very early runtime stage
	 * with the following code:
	 * ```php
	 *      use spaf\simputils\Settings;
	 *      Settings::redefine_pd($your_obj->$method_name(...));
	 *      // or using anonymous functions
	 *      Settings::redefine_pd(
	 *          function (...$args) {
	 *              echo "MY CALLBACK IS BEING USED\n";
	 *              print_r($args);
	 *              die;
	 *          }
	 *      );
	 * ```
	 *
	 * @param ...$args
	 *
	 * @see \die()
	 *
	 * @see \print_r()
	 */
	public static function pd(...$args): never {
		if (Settings::is_redefined(Settings::REDEFINED_PD)) {
			$callback = Settings::get_redefined(Settings::REDEFINED_PD);
			$callback(...$args);
		} else {
			foreach ($args as $arg) {
				print_r($arg);
				echo "\n";
			}
			die();
		}
	}
}