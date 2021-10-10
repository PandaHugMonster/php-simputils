<?php
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpNeverTypedFunctionReturnViolationInspection */

namespace spaf\simputils;


use Exception;
use ReflectionClass;
use spaf\simputils\components\InternalMemoryCache;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\Version;
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
 * Contains fix of the PHP platform "issues" and "missing features", like really disappointing
 * serialize() feature that does not provide ability to use "json" as output.
 *
 * @package spaf\simputils
 */
class PHP {

	const SERIALIZATION_TYPE_JSON = 0;
	const SERIALIZATION_TYPE_PHP = 1;

	public static array $array_yes = [
		'enabled', 'yes', 't', 'true', 'y', '\+', '1'
	];
	public static array $array_no = [
		'disabled', 'no', 'f', 'false', 'n', '-', '0'
	];

	// TODO Maybe #class? Checkout compatibility with JavaScript and other techs and standards
	public static string $serialized_class_key_name = '_class';
	public static string|int $serialization_mechanism = self::SERIALIZATION_TYPE_JSON;

	/**
	 * Serialize any data
	 *
	 * @param mixed $data Data to serialize
	 *
	 * @return ?string
	 *
	 * @throws \Exception Runtime resources can't be serialized
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
	 * @param ?string $str   String to deserialize
	 * @param ?string $class Class hint
	 *
	 * @return mixed
	 * @throws \ReflectionException Reflection related exceptions
	 */
	public static function deserialize(string|null $str, ?string $class = null): mixed {
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

	/**
	 * @param string $str Serialized string
	 *
	 * @return string|null
	 */
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

	/**
	 * Check if object/class using a trait
	 *
	 * @param object|string $class_ref Object or class to check
	 * @param string        $trait_ref Trait string reference
	 *
	 * @return bool
	 */
	public static function classUsesTrait(object|string $class_ref, string $trait_ref): bool {
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

	/**
	 * Check if a string is JSON parsable
	 *
	 * @param string $json_or_not String to check
	 *
	 * @return bool
	 */
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
	 * @param mixed ...$args Multiple params to print out before die
	 *
	 * @see \die()
	 *
	 * @see \print_r()
	 * @return void
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

	/**
	 * Turn bool true or false into string "true" or "false"
	 *
	 * Opposite functionality of {@see \spaf\simputils\PHP::asBool()}.
	 *
	 * @param bool|null $var Value to convert
	 *
	 * @see \spaf\simputils\PHP::asBool()
	 * @return string|null
	 */
	public static function boolStr(bool|null $var): ?string {
		// TODO Improve
		return $var?'true':'false';
	}

	/**
	 * Delete file or directory
	 *
	 * This function should be used in the most cases for both deletion of regular files or
	 * directories. But, for some cases, if you want you can supply `$strict` param as true,
	 * in this case the function will delete only regular files, and raise exception if directory
	 * path is supplied.
	 *
	 * @param string|null $file_path   File path
	 * @param bool        $recursively Recursively delete files (only in case of directories)
	 * @param bool        $strict      If true supplied - then exception is raised in case of
	 *                                 directory path supplied instead of a regular file path.
	 *
	 * @return bool|null
	 * @throws \Exception Exception if `$strict` param is true and the file path provided is
	 *                    a directory.
	 */
	public static function rmFile(
		?string $file_path,
		bool $recursively = false,
		bool $strict = false
	): ?bool {
		if (empty($file_path)) {
			return null;
		}

		if (!file_exists($file_path)) {
			return true;
		}

		if (is_dir($file_path)) {
			if ($strict) {
				// TODO Fix exception
				throw new Exception("{$file_path} is a directory, and a strict mode is on");
			} else {
				return static::rmDir($file_path, $recursively);
			}
		}

		return unlink($file_path);
	}

	/**
	 * Removes only directories
	 *
	 * Recommended to use {@see static::rmFile()} instead when applicable
	 *
	 * @param string|null $directory_path Directory path
	 * @param bool        $recursively    Recursively deletes directories (required if not empty)
	 *
	 * @return bool|null
	 * @throws \Exception Exception if regular file path is supplied, and not a directory path
	 * @todo Add root dir protection
	 */
	public static function rmDir(?string $directory_path, bool $recursively = false): ?bool {
		if (!is_dir($directory_path)) {
			// TODO Fix exception
			throw new Exception("{$directory_path} is not a directory");
		}
		if ($recursively) {
			$res = false;
			$files = static::listFiles($directory_path, true, 'rsort');
			foreach ($files as $file) {
				// Attention: Recursion is here possible in case of directories
				$res = static::rmFile($file, $recursively) || $res;
			}

			return static::rmFile($directory_path) || $res;
		}

		return rmdir($directory_path);
	}

	/**
	 * List files in the folder recursively or not
	 *
	 * In case of file provided instead of folder path, will be returned an array containing
	 * just a name of the file (if not excluded).
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
	 *      // Would output recursively content of your /tmp folder unsorted (on the order
	 *      // of processing/looking up)
	 *
	 *      $dir = '/tmp';
	 *      $res = PHP::listFiles($dir, true, 'rsort');
	 *      print_r($res);
	 *      // Would output recursively content of your /tmp folder in a reversed sort order
	 *
	 * ```
	 *
	 * @param ?string     $file_path             File path
	 * @param bool        $recursively           Recursively create directories
	 * @param bool|string $sorting               True/False or sorting callable
	 *                                           like "sort" or "rsort"
	 * @param bool        $exclude_original_path Exclude original file path from the array.
	 *                                           Default is true, and in the most cases it's fine.
	 *
	 * @return ?array
	 */
	public static function listFiles(
		?string $file_path,
		bool $recursively = false,
		bool|string $sorting = true,
		bool $exclude_original_path = true
	): ?array {
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
					$sub_list = static::listFiles(
						$full_sub_file_path,
						$recursively,
						exclude_original_path: false
					);
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
	 * @param string|null $directory_path Directory path
	 * @param bool        $recursively    Should be directories recursively created
	 *
	 * @return bool|null
	 *@see \mkdir()
	 */
	public static function mkDir(?string $directory_path, bool $recursively = true): ?bool {
		if (!file_exists($directory_path))
			return mkdir($directory_path, recursive: $recursively);

		return true;
	}

	/**
	 * Create file
	 *
	 * @param string|null $file_path   File path
	 * @param mixed       $content     Content to put to file
	 * @param bool        $recursively Create folders recursively
	 *
	 * @return bool|null
	 * @see \file_put_contents()
	 */
	public static function mkFile(
		?string $file_path,
		mixed $content = null,
		bool $recursively = true
	): ?bool {
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
	 * @param string|null $file_path File path
	 *
	 * @todo Implement parser callback parameter
	 * @return string|false|null
	 */
	public static function getFileContent(?string $file_path = null): null|string|false {
		if (empty($file_path) || !file_exists($file_path))
			return false;

		return file_get_contents($file_path);
	}

	/**
	 * Tries to recognize string or other types of value as bool TRUE or FALSE
	 *
	 * Decision is made based on {@see static::$array_yes}, what will not match, will be
	 * considered as FALSE, otherwise TRUE.
	 *
	 * Values can be modified, so you can control what should be considered as TRUE or FALSE
	 *
	 * If `$strict` supplied - then null will be returned if value does not match any of those 2
	 * arrays (yes and no).
	 *
	 * @param mixed $val    Converts string (or any) value into a bool value
	 *                      (recognition is based on $array_yes)
	 * @param bool  $strict If true - then null is returned if the value does not match both arrays
	 *
	 * @return ?bool
	 */
	public static function asBool(mixed $val, bool $strict = false): ?bool {
		$sub_res = false;
		if (!isset($val))
			return false;
		if (in_array($val, static::$array_yes))
			$sub_res = true;
		if ($strict) {
			if ($sub_res)
				return true;
			if (in_array($val, static::$array_no))
				return false;

			return null;
		}
		return $sub_res;
	}

	/**
	 * @return \spaf\simputils\models\Version|string
	 */
	public static function phpVersion(): Version|string {
		return new Version(phpversion());
	}

	/**
	 * @param bool $use_fresh Generate a new object even if it exists in the cache
	 *
	 * @return \spaf\simputils\models\PhpInfo|array|string
	 */
	public static function info(bool $use_fresh = false): PhpInfo|array|string {
		if ($use_fresh || empty(InternalMemoryCache::$default_phpinfo_object)) {
			InternalMemoryCache::$default_phpinfo_object = new PhpInfo();
		}
		return InternalMemoryCache::$default_phpinfo_object;
	}
}
