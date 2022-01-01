<?php
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpNeverTypedFunctionReturnViolationInspection */

namespace spaf\simputils;


use ArrayAccess;
use ArrayObject;
use DateTimeZone;
use Exception;
use Iterator;
use ReflectionClass;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\File;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\Version;
use spaf\simputils\special\CodeBlocksCacheIndex;
use spaf\simputils\special\CommonMemoryCacheIndex;
use spaf\simputils\traits\MetaMagic;
use Throwable;
use function class_exists;
use function class_parents;
use function dirname;
use function is_array;
use function is_dir;
use function is_null;
use function is_object;
use function is_resource;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error;
use function serialize;
use function unserialize;
use const JSON_ERROR_NONE;


/**
 * Special static PHP helper
 *
 * Contains fix of the PHP platform "issues" and "missing features", like really disappointing
 * serialize() feature that does not provide ability to use "json" as output.
 *
 * Regarding {@see \spaf\simputils\PHP::$use_box_instead_of_array}.
 * This functionality set into "true" by default. You can disable it if you are experiencing issues
 * with execution speed. Most likely, usage of Box instead of array for non-gigantic "arrays"
 * should not compromise your performance. THOUGH keep in mind, Box is not as efficient as arrays.
 * Especially if you will be implementing your own Box class and overriding some of it's methods.
 *
 * TODO Checkout and make sure all works efficiently enough etc.
 *
 * @see Box
 * @package spaf\simputils
 */
class PHP {

	const SERIALIZATION_TYPE_JSON = 0;
	const SERIALIZATION_TYPE_PHP = 1;

	// TODO Maybe #class? Checkout compatibility with JavaScript and other techs and standards
	public static string $serialized_class_key_name = '_class';
	public static string|int $serialization_mechanism = self::SERIALIZATION_TYPE_JSON;

	/**
	 * @var bool Using Box object instead of array for the most of stuff related to "Objects"
	 *           read a bit more in the description of this class {@see \spaf\simputils\PHP}
	 *
	 * @see \spaf\simputils\traits\MetaMagic::toArray()
	 */
	public static bool $use_box_instead_of_array = true;

	public static bool $allow_dying = true;

	/**
	 * @var bool $refresh_php_info_env_vars If set, the PHP info object's env variables are
	 *                                      refreshed when updated through `PHP::envSet()` or
	 *                                      `env_set()`
	 */
	public static bool $refresh_php_info_env_vars = true;

	/**
	 * Initializer of the framework
	 *
	 * Should be called just once by any code-group (Main app, independent libraries)
	 *
	 * If init can not be called multiple times for the same `$name` (even for the main "app").
	 * If this is done, it will raise an Exception.
	 *
	 * It's suggested to provide your configs through the `InitConfig` object (please feel free to
	 * extend it by your class for any purpose).
	 *
	 * IMP  For security reasons and efficiency-wise strongly recommended to call `PHP::init()`
	 *      as early as possible in your main app. It should be the very first thing to be called
	 *      right after the "composer autoloader".
	 *
	 * IMP  Modules/Libraries/Extensions and any external code that calls `PHP::init()` without
	 *      `$name` or with value of "app" - must be considered as unsafe!
	 *
	 * IMP  `$name` argument must be always supplied (through `$config` or through `$name`).
	 *      For the security reasons name must be unique and during runtime persist as final.
	 *      So multiple libraries can not use the same name.
	 *
	 * NOTE `$name` parameter can be omit, in this case code will be consider as the "app code",
	 *      and not "module/library/extension code". Modules/Libraries/Extensions MUST NEVER call
	 *      `PHP::init()` without $name parameter, and not use reserved word "app".
	 *      If you are developing the "leaf" code (main app, and not a library) - then
	 *      you should not specify `$name` or you can set it to "app" which is being default.
	 *
	 */
	public static function init(
		null|BasicInitConfig|Box|array $config = null,
		?string $name = null,
		?string $code_root = null,
		?string $working_dir = null
	): BasicInitConfig {
		if (empty($config)) {
			$config = new InitConfig();
		}
		if (is_array($config) || $config instanceof Box) {
			$config = new InitConfig(...$config);
		}
		$config->name = $name ?? $config->name;
		$code_root = $code_root ?? $config->code_root;
		$working_dir = $working_dir ?? $config->working_dir;


		$config->code_root = $code_root ?? debug_backtrace()[0]['file'];
		$config->working_dir = $working_dir ?? $config->code_root;

		// FIX  Implement code below into config through Properties
		if (!is_dir($config->code_root)) {
			$config->code_root = dirname($config->code_root);
		}
		if (!is_dir($config->working_dir)) {
			$config->working_dir = dirname($config->working_dir);
		}
		////

		if (CodeBlocksCacheIndex::registerInitBlock($config)) {
			$config->init();
		} else {
			// TODO Exception here?
		}
		return $config;
	}

	public static function getInitConfig(?string $name = null): ?BasicInitConfig {
		return CodeBlocksCacheIndex::getInitBlock($name);
	}

	/**
	 * Serialize any data
	 *
	 * @param mixed $data          Data to serialize
	 * @param ?int  $enforced_type Enforced serialization type (per function call
	 *                                overrides the default serialization type)
	 *
	 * @return ?string
	 *
	 * TODO Unrelated: Implement recursive toJson control to objects (So object can decide,
	 *      whether it wants to be a string, an array or a number).
	 *
	 * @throws \Exception Runtime resources can't be serialized
	 */
	public static function serialize(mixed $data, ?int $enforced_type = null): ?string {
		if (is_resource($data))
			throw new Exception(
				'Resources cannot be serialized through PHP default mechanisms'
			);

		if (is_null($enforced_type)) {
			$enforced_type = static::$serialization_mechanism;
		}

		if ($enforced_type === static::SERIALIZATION_TYPE_JSON) {
			if (
				(is_object($data) || static::isClass($data))
				&& static::classUsesTrait($data, MetaMagic::class)
			) {
				$res = $data::_metaMagic($data, '___serialize');
			} else {
				$res = $data;
			}

			return json_encode($res);
		}
		if ($enforced_type === static::SERIALIZATION_TYPE_PHP) {
			return \serialize($data);
		}

		return null;
	}

	/**
	 * Deserialize data serialized by {@see serialize()} method
	 *
	 * @param ?string $str           String to deserialize
	 * @param ?string $class         Class hint
	 * @param ?int    $enforced_type Enforced serialization type (per function call
	 *                               overrides the default serialization type)
	 *
	 * @return mixed
	 * @throws \ReflectionException Reflection related exceptions
	 */
	public static function deserialize(
		string|null $str,
		?string $class = null,
		?int $enforced_type = null
	): mixed {
		if (empty($str))
			return null;

		if (empty($class))
			$class = static::determineSerializedClass($str);

//		if (empty($class))
//			// TODO Fix this exception to a more appropriate one
//			throw new Exception('Cannot determine class for deserialization');

		if (is_null($enforced_type)) {
			$enforced_type = static::$serialization_mechanism;
		}

		if ($enforced_type === static::SERIALIZATION_TYPE_JSON) {
			$data = json_decode($str, true);
			if (empty($class)) {
				return $data;
			}
			$dummy = PHP::createDummy($class);
			if (static::classUsesTrait($class, MetaMagic::class)) {
				/** @noinspection PhpUndefinedMethodInspection */
				return $class::_metaMagic($dummy, '___deserialize', $data);
			} else {
				foreach ($data as $key => $val) {
					$dummy->$key = $val;
				}
				return $dummy;
			}
		} else if ($enforced_type === static::SERIALIZATION_TYPE_PHP) {
			return \unserialize($str);
		}

		return null;
	}

	/**
	 * @param string $str Serialized string
	 *
	 * @todo Maybe automatically determine the serialization type?
	 * @return string|null
	 */
	private static function determineSerializedClass(string $str): ?string {
		$data = json_decode($str, true);
		// JSON parsing
		if (json_last_error() === JSON_ERROR_NONE) {
			if (is_array($data) && !empty($data[static::$serialized_class_key_name])) {
				return $data[static::$serialized_class_key_name];
			}
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
	 * PHP Version
	 *
	 * @return Version|string
	 */
	public static function version(): Version|string {
		$class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);
		return new $class(phpversion(), 'PHP');
	}

	/**
	 * Framework/lib version
	 *
	 * @return Version|string
	 */
	public static function simpUtilsVersion(): Version|string {
		$class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);
		return new $class('0.2.3', 'SimpUtils');
	}

	/**
	 * Framework/lib license
	 *
	 * @return string
	 */
	public static function simpUtilsLicense(): string {
		return 'MIT';
	}

	/**
	 * @param bool $use_fresh Generate a new object even if it exists in the cache
	 *
	 * @return \spaf\simputils\models\PhpInfo|array|string
	 */
	public static function info(bool $use_fresh = false): PhpInfo|array|string {
		if ($use_fresh || empty(CommonMemoryCacheIndex::$default_phpinfo_object)) {
			$class = CodeBlocksCacheIndex::getRedefinition(
				InitConfig::REDEF_PHP_INFO,
				PhpInfo::class
			);
			CommonMemoryCacheIndex::$default_phpinfo_object = new $class();
		}
		return CommonMemoryCacheIndex::$default_phpinfo_object;
	}

	/**
	 * Identifies variable type
	 *
	 * @param mixed $var Variable to identify
	 *
	 * @return string
	 */
	public static function type(mixed $var): string {
		return is_object($var)?get_class($var):gettype($var);
	}

	/**
	 * Check if provided value is a class string
	 *
	 * @param mixed $class_or_not Value that is being checked
	 *
	 * @return bool
	 */
	public static function isClass(mixed $class_or_not): bool {
		if (is_string($class_or_not)) {
			if (class_exists($class_or_not, false))
				return true;
		}
		return false;
	}

	/**
	 * Checks if class of first variable is found in second (as one of parents or itself)
	 *
	 * In case if non-strict (default behaviour) check - instead of string class refs object could
	 * be used (then their classes will be compared)
	 *
	 * Second argument as array allows to check against multiple classes/objects.
	 * But keep in mind each class's parents will be checked, and if found there - you will get
	 * true as a result.
	 *
	 * @param string|object       $item             Class or object to check
	 * @param string|object|array $of_item          Class or object against of which to check
	 * @param bool                $disallow_objects If true, then objects will cause "FALSE"
	 *
	 * @return bool
	 */
	public static function isClassIn(
		string|object $item,
		string|object|array $of_item,
		bool $disallow_objects = false
	): bool {
		if (is_object($item)) {
			if ($disallow_objects) {
				return false;
			}
			$item = $item::class;
		}
		if (is_object($of_item)) {
			if ($disallow_objects) {
				return false;
			}

			$of_item = $of_item::class;
		}

		if (static::isClass($item) && static::isClass($of_item)) {
			if ($item === $of_item) {
				return true;
			}

			$parents = class_parents($of_item, false);
			foreach ($parents as $parent) {
				if ($item === $parent) {
					return true;
				}
			}
		}


		return false;
	}

	/**
	 * Check if first class contains the second one (as itself or one of his parents)
	 *
	 * Basically {@see static::isClassIn()} shortcut, but with inversed first 2 arguments.
	 * With a tiny limitation - arrays should not be used in here as any of arguments
	 *
	 * ```php
	 *
	 *  use spaf\simputils\PHP;
	 *
	 *  class A {}
	 *  class B extends A {}
	 *  class C {}
	 *
	 *  $b_contains_a = PHP::classContains(B::class, A::class);
	 *  // Returns true, because B class is extended from A
	 *
	 *  $a_contains_c = PHP::classContains(A::class, C::class);
	 *  // Returns false, Because A class is not C class and not having C class as one of it's
	 *  // parents
	 *
	 *  $a_contains_b = PHP::classContains(A::class, B::class);
	 *  // Returns false, Because A class is independent from B class (B extended from A,
	 *  // not vice-versa)
	 *
	 *  $c_contains_c = PHP::classContains(C::class, C::class);
	 *  // Returns true, Because C class is C class
	 *
	 * ```
	 *
	 * @param string|object $of_item          Class or object
	 * @param string|object $item             Class or object being part of the first argument
	 * @param bool          $disallow_objects Limit objects usage, in case of true value
	 *                                        That would lead to false if objects instead of
	 *                                        classes provided
	 *
	 * @todo Add traits and interfaces checks
	 * @todo Array "as-any" of classes/traits/interfaces
	 *
	 * @return bool
	 */
	public static function classContains(
		string|object $of_item,
		string|object $item,
		bool $disallow_objects = false
	): bool {
		return static::isClassIn($item, $of_item, $disallow_objects);
	}

	/**
	 * Creates dummy object (without calling __construct)
	 *
	 * This way of creating objects should be avoided in the most of the cases. It's needed
	 * only if you are working with serialization-alike functionality.
	 *
	 * @param string|object $class Class or Object (class of the provided object then will be taken)
	 *
	 * @return object
	 * @throws \ReflectionException Reflection exception
	 */
	public static function createDummy(string|object $class): object {
		if (is_object($class)) {
			$class = $class::class;
		}
		return (new ReflectionClass($class))->newInstanceWithoutConstructor();
	}

	/**
	 * Determines whether value is array-alike (can be treated as array)
	 *
	 * Basically it checks presence of {@see Iterator} + {@see ArrayAccess} interfaces (must have
	 * both), or if the variable type is "array".
	 *
	 * **Important:** Strings are not considered as array-alike, so this method will return FALSE
	 * if a string provided.
	 *
	 * @param mixed $var Any value
	 *
	 * @todo Subject to partial improvement after {@see static::classContains()} fixing
	 * @return bool
	 * @throws \ReflectionException Temporary
	 */
	public static function isArrayCompatible(mixed $var): bool {
		if (is_array($var)) {
			return true;
		}

		if (is_object($var)) {
			$var = $var::class;
		}

		if (static::isClass($var)) {
			// TODO This should be implemented through static::classContains
			$reflection = new ReflectionClass($var);
			if ($reflection->isSubclassOf(ArrayObject::class)) {
				return true;
			}
		}

		return false;
	}

	//// Methods with shortcutting into "basic.php" file

	/**
	 * Please Die function
	 *
	 * Print out all the supplied params, and then die/exit the runtime.
	 * Basically, could be considered as a shortcut of sequence of "print_r + die"
	 *
	 * Besides that, the functionality can be redefined. For example if you want
	 * use your own implementation, you can just redefine it on a very early runtime stage
	 * with the following code:
	 * FIX  Prepare example!
	 *
	 * @todo implement simple log integration
	 * FIX  Implement $allow_dying
	 *
	 * @param mixed ...$args Anything you want to print out before dying
	 *
	 * @see \die()
	 *
	 * @see \print_r()
	 * @return void
	 */
	public static function pd(...$args) {
		if ($callback = CodeBlocksCacheIndex::getRedefinition(InitConfig::REDEF_PD)) {
			$res = (bool) $callback(...$args);
		} else {
			foreach ($args as $arg) {
				print_r($arg);
				echo "\n";
			}
			$res = true;
		}
		if (static::$allow_dying && $res) {
			die(); // @codeCoverageIgnore
		}
	}

	/**
	 * @param ?array $array Array, elements of which should be used as elements of the newly created
	 *                      box.
	 *
	 * TODO Implement transparent Box supplying instead of array?
	 *
	 * @return Box|array
	 */
	public static function box(?array $array = null): Box|array {
		$class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_BOX,
			Box::class
		);
		return new $class($array);
	}

	/**
	 * Just a shortcut for `DateTimeHelper::now`
	 *
	 * @param \DateTimeZone|null $tz TimeZone
	 *
	 * @return DateTime|null
	 *
	 * @throws \Exception Parsing error
	 */
	public static function now(?DateTimeZone $tz = null): ?DateTime {
		return DT::now($tz);
	}

	/**
	 * Just a simplified shortcut for `DateTimeHelper::normalize`
	 *
	 * @param DateTime|string|int $dt  Any date-time representation (DateTime object, string, int)
	 * @param \DateTimeZone|null  $tz  TimeZone
	 * @param string|null         $fmt FROM Format, usually not needed, just if you are using
	 *                                 a special date-time format to parse
	 *
	 * @return DateTime|null
	 *
	 * @throws \Exception Parsing error
	 */
	public static function ts(
		DateTime|string|int $dt,
		?DateTimeZone $tz = null,
		string $fmt = null
	): ?DateTime {
		return DT::normalize($dt, $tz, $fmt);
	}

	public static function file(null|string|File $file = null, $app = null): ?File {
		$class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_FILE,
			File::class
		);
		return new $class($file, $app);
	}

	/**
	 * Just a "shortcut" to $_ENV
	 *
	 * You would think why it's done like this, but situation in PHP is so weird in matter of
	 * Env Vars - so it's kind of a single point of usage when you feel comfortable with it.
	 * You really don't need to use this method if you feel weird about it. Using `$_ENV` is fully
	 * normal way, and even somehow comfortable for "in line {$_ENV['smthg']} usage" :).
	 *
	 * @return array|Box
	 */
	#[Shortcut('\$_ENV')]
	public static function allEnvs(): array|Box {
		return CommonMemoryCacheIndex::$initial_get_env_state ?? [];
	}

	/**
	 * Get Environmental Variable
	 *
	 * Due to thread-unsafe nature of `putenv()` and `getenv()`, those are completely unused,
	 * on a level of the framework. It's strongly recommended to use `PHP::env()` or
	 * `\spaf\simputils\basic\env()` and `PHP::envSet()` or `\spaf\simputils\basic\env_set()`.
	 *
	 *
	 * @param string $name Env variable name
	 *
	 * @return mixed Returns value, or null if does not exist
	 */
	public static function env(?string $name = null, bool $strict = true): mixed {
		if (empty($name)) {
			return static::allEnvs();
		}
		return $_ENV[$name] ?? null;
	}

	/**
	 * Setting Environmental Variable for this runtime
	 *
	 * IMP  This does not change the real Environmental Variables, it does not propagate to other
	 *      threads or processes. It just adds value to `$_ENV` array.
	 *
	 * IMP  The PhpInfo env_vars updated only on the main object received through `PHP::info()`
	 *
	 * IMP  `getenv()` will never return values assigned by this method, it's recommended against
	 *      of using `getenv()` because it's thread-unsafe nature (and `putenv()` as well).
	 *
	 * @param string $name     Environmental variable name
	 * @param mixed  $value    Value to set
	 * @param bool   $override If the value is not empty and this parameter is false (default) -
	 *                         then the value will not be overwritten. In case if the variable is
	 *                         empty - then this parameter ignored. If it is set to true - then
	 *                         in any case it would be overwritten.
	 *                         **Important:** In the most cases it's almost always a bad idea to
	 *                         overwrite/override the set value, because it could override
	 *                         intentionally set Env Variable from the container/script/os like
	 *                         "special keys" or even "secrets".
	 * @see static::info()
	 */
	public static function envSet(string $name, mixed $value, bool $override = false): void {
		if (empty($_ENV[$name]) || $override) {
			$_ENV[$name] = $value;
			if (static::$refresh_php_info_env_vars) {
				$info = static::info();
				$info->updateEnvVar($name, $value);
			}
		}
	}
}
