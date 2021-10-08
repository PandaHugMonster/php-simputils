<?php
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpNeverTypedFunctionReturnViolationInspection */

namespace spaf\simputils;


use Exception;
use ReflectionClass;
use spaf\simputils\traits\MetaMagic;
use Throwable;
use function array_merge;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function is_array;
use function is_callable;
use function is_dir;
use function is_null;
use function is_resource;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function serialize;
use function sort;
use function unlink;
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
	public static function serialize(mixed $data): ?string {
		if (is_resource($data))
			throw new Exception('Resources cannot be serialized through PHP default mechanisms');

		if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_JSON) {
			if (static::classUsesTrait($data, MetaMagic::class)) {
				$res = $data::_metaMagic($data, '___serialize');
			} else {
				$res = $data;
			}

			return json_encode($res);
		}
		if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_PHP) {
			return \serialize($data);
		}

		return null;
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
			$class = static::determineSerializedClass($str);

		if (empty($class))
			// TODO Fix this exception to a more appropriate one
			throw new Exception('Cannot determine class for deserialization');

		if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_JSON) {
			$data = json_decode($str, true);

			if (static::classUsesTrait($class, MetaMagic::class)) {
				$dummy = (new ReflectionClass($class))->newInstanceWithoutConstructor();
				/** @noinspection PhpUndefinedMethodInspection */
				return $class::_metaMagic($dummy, '___deserialize', $data);
			} else {
				$dummy = new $class;
				foreach ($data as $key => $val) {
					$dummy->$key = $val;
				}
				return $dummy;
			}
		} else if (static::$serialization_mechanism === static::SERIALIZATION_TYPE_PHP) {
			return \unserialize($str);
		}

		return null;
	}

	private static function determineSerializedClass(string $str): ?string {
		$data = json_decode($str, true);
		// JSON parsing
		if (json_last_error() === JSON_ERROR_NONE) {
			if (is_array($data) && !empty($data[static::$serialized_class_key_name]))
				return $data[static::$serialized_class_key_name];
		} else {
			try {
				$res = unserialize($str);
			} catch (Exception $exception) {
				$res = $exception;
			}
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
		foreach (class_uses($class_ref) as $trait) {
			if ($trait == $trait_ref)
				return true;
		}

		return false;
	}

	public static function isJsonString(string $json_or_not): bool {
		json_decode($json_or_not, true);
		if (json_last_error() === JSON_ERROR_NONE)
			return true;
		return false;
	}

	public static bool $allow_dying = true;

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
	public static function pd(...$args) {
		if (Settings::isRedefined(Settings::REDEFINED_PD)) {
			$callback = Settings::getRedefined(Settings::REDEFINED_PD);
			$res = (bool) $callback(...$args);
		} else {
			foreach ($args as $arg) {
				print_r($arg);
				echo "\n";
			}
			$res = true;
		}
		if (static::$allow_dying && $res)
			die(); // @codeCoverageIgnore
	}

	public static function boolStr(bool|null $var): ?string {
		// TODO Improve
		return $var?'true':'false';
	}

	/**
	 * Delete file or directory
	 *
	 * @param string|null $file_path
	 * @param bool $recursively
	 *
	 * @return bool|null
	 * @throws \Exception
	 */
	public static function rmFile(?string $file_path, bool $recursively = false): ?bool {
		if (empty($file_path)) {
			return null;
		}

		if (!file_exists($file_path)) {
			return true;
		}

		if (is_dir($file_path)) {
			return static::rmDir($file_path, $recursively);
		}

		return unlink($file_path);
	}

	/**
	 * Removes only directories
	 *
	 * Recommended to use {@see static::rmFile()} instead when applicable
	 *
	 * @param string|null $file_path
	 * @param bool $recursively
	 *
	 * @todo Add root dir protection
	 * @return bool|null
	 * @throws \Exception
	 */
	public static function rmDir(?string $file_path, bool $recursively = false): ?bool {
		if (!is_dir($file_path)) {
			// TODO Fix exception
			throw new Exception("{$file_path} is not a directory");
		}
		if ($recursively) {
			$res = false;
			$files = static::listFiles($file_path, true, 'rsort');
			foreach ($files as $file) {
				// Attention: Recursion is here possible in case of directories
				$res = static::rmFile($file, $recursively) || $res;
			}

			return static::rmFile($file_path) || $res;
		}

		return rmdir($file_path);
	}

	/**
	 * List files in the folder recursively or not
	 *
	 * In case of file provided instead of folder path, will be returned an array containing just a name of the file
	 * (if not excluded).
	 *
	 * ```php
	 *      $dir = '/tmp';
	 *      $res = PHP::listFiles($dir, true, true);
	 *      print_r($res);
	 *      // Would output recursively content of your /tmp folder sorted from the top
	 *      // Equivalent of PHP::listFiles($dir, true, 'sort');
	 *
	 *      $dir = '/tmp';
	 *      $res = PHP::listFiles($dir, true, false);
	 *      print_r($res);
	 *      // Would output recursively content of your /tmp folder unsorted (on the order of processing/looking up)
	 *
	 *      $dir = '/tmp';
	 *      $res = PHP::listFiles($dir, true, 'rsort');
	 *      print_r($res);
	 *      // Would output recursively content of your /tmp folder in a reversed sort order
	 *
	 * ```
	 *
	 * @param ?string $file_path
	 * @param bool $recursively
	 * @param bool|string $sorting True/False or sorting callable like "sort" or "rsort"
	 * @param bool $exclude_original_path
	 *
	 * @return ?array
	 */
	public static function listFiles(?string $file_path, bool $recursively = false, bool|string $sorting = true, bool $exclude_original_path = true): ?array {
		$res = $exclude_original_path?[]:[$file_path];
		if (file_exists($file_path)) {
			if (!is_dir($file_path))
				return $res;

			// TODO bug here!
			$scanned_dir = scandir($file_path);
			if ($recursively) {
				foreach ($scanned_dir as $file) {
					if (in_array($file, ['.', '..'])) continue;

					$full_sub_file_path = realpath($file_path.'/'.$file);
					$sub_list = static::listFiles($full_sub_file_path, $recursively, exclude_original_path: false);
					if (!empty($sub_list) && is_array($sub_list)) {
						$res = array_merge($res, $sub_list);
					}
				}
			}
		}

		if (!empty($sorting)) {
			if (is_string($sorting) || is_callable($sorting)) {
				$sorting($res);
			} else {
				sort($res);
			}
		}

		return $res;
	}

	/**
	 * Create directory
	 *
	 * @param string|null $file_path
	 * @param bool $recursively
	 *
	 * @see \mkdir()
	 * @return bool|null
	 */
	public static function mkDir(?string $file_path, bool $recursively = true): ?bool {
		if (!file_exists($file_path))
			return mkdir($file_path, recursive: $recursively);

		return true;
	}

	/**
	 * Create file
	 *
	 * @param string|null $file_path
	 * @param mixed $content
	 *
	 * @see \file_put_contents()
	 * @return bool|null
	 */
	public static function mkFile(?string $file_path, mixed $content = null, bool $recursively = true): ?bool {
		if ($recursively) {
			$base_dir = dirname($file_path);
			// Make sure the parent dir for the file is created
			static::mkDir($base_dir);
		}
		if (is_null($content))
			$content = '';
		return (bool) file_put_contents($file_path, $content);
	}

	/**
	 * @param string|null $file_path
	 *
	 * @todo Implement parser callback parameter
	 * @return string|false|null
	 */
	public static function getFileContent(?string $file_path = null): null|string|false {
		if (empty($file_path) || !file_exists($file_path))
			return false;

		return file_get_contents($file_path);
	}

}