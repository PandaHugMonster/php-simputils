<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpNeverTypedFunctionReturnViolationInspection */

namespace spaf\simputils;


use ArrayAccess;
use ArrayObject;
use Exception;
use Generator;
use Iterator;
use ReflectionClass;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\exceptions\RedefUnimplemented;
use spaf\simputils\exceptions\RedefWrongReference;
use spaf\simputils\exceptions\SerializationProblem;
use spaf\simputils\exceptions\UnBoxable;
use spaf\simputils\generic\BasicExecEnvHandler;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\BasicIP;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\Box;
use spaf\simputils\models\BoxRO;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\IPv4;
use spaf\simputils\models\PhpInfo;
use spaf\simputils\models\Set;
use spaf\simputils\models\StackFifo;
use spaf\simputils\models\StackLifo;
use spaf\simputils\models\UrlObject;
use spaf\simputils\models\Version;
use spaf\simputils\special\CodeBlocksCacheIndex;
use spaf\simputils\special\CommonMemoryCacheIndex;
use spaf\simputils\traits\MetaMagic;
use Throwable;
use function class_exists;
use function class_parents;
use function defined;
use function dirname;
use function intval;
use function is_array;
use function is_dir;
use function is_null;
use function is_object;
use function is_resource;
use function json_decode;
use function json_encode;
use function json_last_error;
use function method_exists;
use function ob_get_clean;
use function ob_start;
use function print_r;
use function serialize;
use function str_contains;
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
 * @see Box
 * @package spaf\simputils
 */
class PHP {

	const SERIALIZATION_TYPE_JSON = 0;
	const SERIALIZATION_TYPE_PHP = 1;

	const STACK_LIFO = 'lifo';
	const STACK_FIFO = 'fifo';

	public static string $serialized_class_key_name = '#class';
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

	public static function frameworkDir() {
		return __DIR__;
	}

	/**
	 * Framework/lib version
	 *
	 * IMP  Always update version info before every release
	 * @return Version|string
	 */
	public static function simpUtilsVersion(): Version|string {
		$class = static::redef(Version::class);
		return new $class('1.1.4', 'SimpUtils');
	}

	/**
	 * List of mime-types of PHP executables
	 *
	 * Only PHP-related mime-types!
	 *
	 * @return \spaf\simputils\models\Box
	 */
	static function listOfExecPhpMimeTypes(): Box {
		return static::box([
			'application/x-php', // This one usually preferable

			'text/x-php',
			'application/x-httpd-php', 'application/x-httpd-php-source', // Debian-related

			'application/php', 'text/php' // if to follow IANA - those are not correct
		]);
	}

	/**
	 * List of file extensions of PHP executables
	 *
	 * @return \spaf\simputils\models\Box
	 */
	static function listOfExecPhpFileExtensions(): Box {
		return static::box([
			'php', // normal

			'phps', 'php5', 'php4', 'php3', 'phtml' // just a few, list might be extended later
		]);
	}

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
	 */
	public static function init(null|array|Box|BasicInitConfig $args = null): BasicInitConfig {

		$config = null;
		if ($args instanceof BasicInitConfig) {
			$config = $args;
			$args = [];
		}

		$config = $config ?? new InitConfig;

		$config->code_root = $config->code_root ?? debug_backtrace()[0]['file'];
		$config->working_dir = $config->working_dir ?? $config->code_root;

		static::metaMagicSpell($config, 'setup', $args ?? []);

		if (empty($config->ee)) {
			$config->ee = BasicExecEnvHandler::EE_UNKNOWN;
		}

		// TODO Implement code below into config through Properties
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

	private static $_cached_current_url = null;

	/**
	 * @param $refresh
	 *
	 * @return ?UrlObject
	 */
	static function currentUrl($refresh = false) {
		if (static::isCLI() && !defined('CURRENT_URL_PRETEND_NOT_CLI')) {
			return null;
		}
		if (!static::$_cached_current_url || $refresh) {
			$info = static::info();
			$serv = $info->server_var;
			$protocol = $serv['HTTPS']?'https':'http';
			$host = $serv['SERVER_NAME'] ?? null;
			if (empty($host)) {
				$host = $serv['HTTP_HOST'] ?? null;
			}

			$server_port = $serv['SERVER_PORT'] ?? null
				?intval($serv['SERVER_PORT'])
				:null;

			$uri = $serv['REQUEST_URI'] ?? null;

			static::$_cached_current_url = static::url(
				host: $host,
				path: $uri,
				protocol: $protocol,
				port: $server_port
			);
		}

		return static::$_cached_current_url;
	}

	public static function metaMagicSpell(string|object $ref, $spell, ...$args) {
		$spell = Str::removeStarting($spell, '___');
		$spell = "___{$spell}";
		if ((static::isClass($ref) || is_object($ref)) && method_exists($ref, '_metaMagic')) {
			return $ref::_metaMagic($ref, $spell, ...$args);
		}

		return null; // @codeCoverageIgnore
	}

	public static function getInitConfig(?string $name = null): ?BasicInitConfig {
		return CodeBlocksCacheIndex::getInitBlock($name);
	}

	/**
	 * @param string|null $name Name of the init config. If empty, used the main "app" InitConfig
	 *
	 * @return \spaf\simputils\generic\BasicInitConfig|null
	 */
	#[Shortcut('static::getInitConfig()')]
	public static function ic(?string $name = null): ?BasicInitConfig {
		return static::getInitConfig($name);
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
	 * TODO Important:: Implement recursive toJson control to objects (So object can decide,
	 *      whether it wants to be a string, an array or a number).
	 *
	 */
	public static function serialize(mixed $data, ?int $enforced_type = null): ?string {
		if (is_resource($data))
			throw new SerializationProblem(
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
				$res = static::metaMagicSpell($data, 'serialize');
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
	 * TODO Deserialization does not recursively uses boxes instead of arrays.
	 *      Should be fixed!!
	 * @return mixed
	 * @throws \ReflectionException Reflection related exceptions
	 */
	public static function deserialize(
		?string $str,
		?string $class = null,
		?int $enforced_type = null
	): mixed {
		if (empty($str)) {
			return null;
		}

		if (empty($class)) {
			$class = static::determineSerializedClass($str);
		}

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
				return static::metaMagicSpell($dummy, 'deserialize', $data);
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
	 * It checks recursively (it checks on the target class, on all the parent classes,
	 * and on all the sub-traits recursively)
	 *
	 * @param object|string $class_ref Object or class to check
	 * @param string        $trait_ref Trait string reference
	 *
	 * @return bool
	 */
	public static function classUsesTrait(object|string $class_ref, string $trait_ref): bool {
		foreach (class_parents($class_ref) as $cp) {
			if (static::_iterateTraitsOver($cp, $trait_ref)) {
				return true;
			}
		}

		if (static::_iterateTraitsOver($class_ref, $trait_ref)) {
			return true;
		}

		return false;
	}

	private static function _iterateTraitsOver($cp, $trait_ref): bool {
		$traits = class_uses($cp);
		if (!empty($traits)) {
			foreach ($traits as $trait) {
				if ($trait == $trait_ref || static::_iterateTraitsOver($trait, $trait_ref)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * PHP Version
	 *
	 * @return Version|string
	 */
	public static function version(): Version|string {
		$class = static::redef(Version::class);
		return new $class(phpversion(), 'PHP');
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
			$class = static::redef(PhpInfo::class);
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
		$res = is_object($var)?get_class($var):gettype($var);
		if ($res === 'double') {
			return 'float';
		}

		return $res;
	}

	/**
	 * Check if provided value is a class string
	 *
	 * @param mixed $class_or_not Value that is being checked
	 *
	 * @return bool
	 */
	public static function isClass(mixed $class_or_not): bool {
		if (Str::is($class_or_not)) {
			if (class_exists($class_or_not, true)) {
				return true;
			}
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
	 * Creates dummy object (without calling constructor)
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
	 * Determines whether value is array-alike (can be treated as an array)
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

	 * TODO implement simple log integration
	 * TODO Prepare example!
	 *
	 * @param mixed ...$args Anything you want to print out before dying
	 *
	 * @see \die()
	 *
	 * @see \spaf\simputils\basic\pr()
	 * @see \print_r()
	 * @return void
	 */
	static function pd(mixed ...$args) {
		$callback = CodeBlocksCacheIndex::getRedefinition(InitConfig::REDEF_PD);
		if ($callback && $callback !== InitConfig::REDEF_PD) {
			$res = (bool) $callback(...$args);
		} else {
			static::pr(...$args);
			$res = true;
		}
		if (static::$allow_dying && $res) {
			die(); // @codeCoverageIgnore
		}
	}

	public static function pr(...$args): void {
		$callback = CodeBlocksCacheIndex::getRedefinition(InitConfig::REDEF_PR);
		if ($callback && $callback !== InitConfig::REDEF_PR) {
			$callback(...$args);
		} else {
			foreach ($args as $arg) {
				print_r($arg);
				echo "\n";
			}
		}
	}

	/**
	 * As `pr()` but returning string or null instead of printing to the buffer
	 *
	 * Basically a shortcut for ob_start() + pr() + ob_get_clean()
	 *
	 * Don't forget to get the result. If you run it without "echo" - then you will not see
	 * output.
	 *
	 * @see \ob_start()
	 * @see PHP::pr()
	 * @see \ob_get_clean()
	 *
	 * @param mixed ...$args Any arguments
	 *
	 * @return string|null
	 */
	public static function prstr(mixed ...$args): ?string {
		if (empty($args)) {
			return null;
		}

		ob_start();
		static::pr(...$args);
		$res = ob_get_clean();

		return $res;
	}

	/**
	 * Quick box-array creation
	 *
	 * **Important:** All the arguments during merging are recursively merged,
	 * when more right-side elements having higher precedence than the left ones, and so
	 * values defined on the left side might be overwritten by the elements to the right.
	 *
	 * @param null|Box|array $array     Array, elements of which should be used as elements
	 *                                  of the newly created box.
	 * @param array|Box      ...$merger Additional arrays/boxes that should be merged into
	 *                                  the resulting Box
	 *
	 * @return Box|array
	 */
	public static function box(mixed $array = null, mixed ...$merger): Box|array {
		$class = static::redef(Box::class);
		return static::_boxAndRo($class, $array, $merger);
	}

	public static function bro(mixed $array = null, mixed ...$merger): Box|array {
		$class = static::redef(BoxRO::class);
		return static::_boxAndRo($class, $array, $merger);
	}

	protected static function _boxAndRo($class, $array, $merger) {
		if ($array instanceof Box) {
			$res = $array;
		} else if (is_null($array)) {
			$res = new $class;
		} else {
			if (is_object($array) && !is_array($array) && !$array instanceof Box) {
				$res = $array;
				if (method_exists($array, 'toBox')) {
					$res = $res->toBox(false);
				} else if ($array instanceof Generator) {
					$res = new $class();
					foreach ($array as $value) {
						$res[] = $value;
					}

				} else {
					throw new UnBoxable("Not possible to use supplied value as 
					argument to box");
				}
			} else {
				$res = new $class($array);
			}
		}

		if (!empty($merger)) {
			$sub_res = new $class;
			foreach ($merger as $k => $v) {
				if (is_object($v) && !is_array($v) && !$v instanceof Box) {
					$sub_sub_res = $v;
					if (method_exists($sub_sub_res, 'toBox')) {
						$sub_sub_res = $sub_sub_res->toBox(false);
					}
					$sub_res[$k] = $sub_sub_res;
				} else {
					$sub_res[$k] = $v;
				}
			}
			$res->mergeFrom(...$sub_res);
		}
		return $res;
	}

	/**
	 * Create a stack object
	 *
	 * @param Box|StackLifo|StackFifo|array|null $items              All the items that should
	 *                                                               be pushed into
	 *                                                               the newly created stack object.
	 *                                                               Must not have "keys".
	 * @param mixed                              ...$merger_and_conf Merger and  keyed-params
	 * @param string                             $type               This key should be explicitly
	 *                                                               specified. Should contain
	 *                                                               "fifo" or "lifo",
	 *                                                               by default is "lifo".
	 *
	 * TODO Improve stack processing, for example if Stack provided, then do not create a new one
	 * @return \spaf\simputils\models\StackFifo|\spaf\simputils\models\StackLifo
	 */
	public static function stack(
		Box|StackLifo|StackFifo|array|null $items = null,
		mixed ...$merger_and_conf
	): StackFifo|StackLifo {
		$class_stack_lifo = static::redef(StackLifo::class);
		$class_stack_fifo = static::redef(StackFifo::class);
		$items = $items ?? [];

		$merger_and_conf = static::box($merger_and_conf);
		$type = $merger_and_conf->get('type', static::STACK_LIFO);
		if ($merger_and_conf->containsKey('type')) {
			$merger_and_conf = $merger_and_conf->unsetByKey('type')->values;
		}
		$items = static::box($items, $merger_and_conf);
		$obj = $type === static::STACK_LIFO
			?new $class_stack_lifo($items)
			:new $class_stack_fifo($items);
		return $obj;
	}

	/**
	 * Creating "Set"
	 *
	 * @param Box|array $items Arguments that will be elements of the Set
	 *
	 */
	public static function set(Box|array|null $items = null): Set {
		$class = static::redef(Set::class);
		$items = $items ?? [];
		return new $class($items);
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
		$class_box = static::redef(Box::class);
		return new $class_box($_ENV ?? CommonMemoryCacheIndex::$initial_get_env_state ?? []);
	}

	/**
	 * Get Environmental Variable
	 *
	 * Due to thread-unsafe nature of `putenv()` and `getenv()`, those are completely unused,
	 * on a level of the framework. It's strongly recommended to use `PHP::env()` or
	 * `\spaf\simputils\basic\env()` and `PHP::envSet()` or `\spaf\simputils\basic\env_set()`.
	 *
	 *
	 * @param string|null $name    Env variable name
	 * @param mixed|null  $default Default value
	 *
	 * @return mixed Returns value, or null if does not exist
	 */
	public static function env(?string $name = null, mixed $default = null): mixed {
		return $_ENV[$name] ?? $default;
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

	/**
	 * Checks whether the runtime is in "cli"/"console"/"terminal" mode or "web"
	 *
	 * It heavily relies on the value of PHP_SAPI, so the identification might not be perfectly
	 * perfect :).
	 *
	 * @return bool Returns true if console, returns false if web
	 */
	public static function isConsole(): bool {
		$sapi_value = Str::lower(static::info()->sapi_name);
		return str_contains($sapi_value, 'cli');
	}

	#[Shortcut('PHP::isConsole()')]
	public static function isCLI(): bool {
		return static::isConsole();
	}

	static function url(
		null|UrlCompatible|string|Box|array $host = null,
		null|Box|array|string $path = null,
		null|Box|array $params = null,
		?string $protocol = null,
		?string $processor = null,
		?string $port = null,
		?string $user = null,
		?string $pass = null,
		mixed ...$data,
	) {
		$class = PHP::redef(UrlObject::class);
		if ($host instanceof $class) {
			/** @var UrlObject $host */
			$host->addPath($path);
			$host->addParams($params);
			return $host;
		}

		$model = new $class(
			$host, $path, $params, $protocol, $processor,
			$port, $user, $pass, ...$data
		);

		return $model;
	}

	static function ip(string|IPv4 $ip) {
		$class = PHP::redef(IPv4::class);
		if ($ip instanceof BasicIP) {
			return $ip;
		}

		$model = new $class($ip);
		return $model;
	}

	/**
	 * Quick and improved version of getting class string of redefinable components
	 *
	 * Shortcut for this:
	 * ```php
	 *      $class = CodeBlocksCacheIndex::getRedefinition(
	 *          InitConfig::REDEF_DATE_TIME,
	 *          DateTime::class
	 *      );
	 * ```
	 *
	 * **Important:** This is one of the internal functionality of the framework. In the most
	 * cases, if you don't know what it is - you should not use it.
	 *
	 * @param string  $target_class Target class, used as default if no redefinition
	 * @param ?string $hint         Hinting name of the redefinable component,
	 *                              usually is not needed when the target class uses
	 *                              `\spaf\simputils\traits\RedefinableComponentTrait`
	 *
	 * @return ?string Returns the final class name string that could be used for creation
	 *                 of objects, and usage of static methods.
	 */
	public static function redef(string $target_class, string $hint = null): ?string {
		if (!static::isClass($target_class)) {
			throw new RedefWrongReference(
				"String \"{$target_class}\" is not a valid class reference"
			);
		}

		if (empty($hint)) {
			if (!method_exists($target_class, 'redefComponentName')) {
				// TODO Maybe default behaviour instead of Exception
				throw new RedefUnimplemented(
					"Class \"{$target_class}\" does not have " .
					"\"redefComponentName\" method, and \$hint argument was not provided"
				);
			}
			$hint = $target_class::redefComponentName();
		}

		return CodeBlocksCacheIndex::getRedefinition($hint, $target_class);
	}

	/**
	 * @param string $key   Validator/Normalizer key
	 * @param string $class Validator/Normalizer class ref
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	public static function setPropertyValidator(string $key, string $class) {
		CommonMemoryCacheIndex::$property_validators[$key] = $class;
	}

	/**
	 * @param string $key Validator/Normalizer key
	 *
	 * @codeCoverageIgnore
	 * @return mixed|string
	 */
	public static function getPropertyValidator(string $key) {
		return CommonMemoryCacheIndex::$property_validators[$key];
	}

	/**
	 * @param int $val Validator/Normalizer appliance level
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	public static function setPropertyValidatorLevel(int $val) {
		CommonMemoryCacheIndex::$property_validators_enabled = $val;
	}

	/**
	 * @codeCoverageIgnore
	 * @return int
	 */
	public static function getPropertyValidatorLevel(): int {
		return CommonMemoryCacheIndex::$property_validators_enabled;
	}

	private static $_get = null;
	private static $_post = null;

	/**
	 * Returns $_POST data wrapped into a Box
	 *
	 * Important: It's caching. Every call will return the same object
	 *
	 * It's name is beyond of the common convention of the library on purpose, to increase
	 * intuitive usage and distinct between "get" methods
	 *
	 * @param bool $refresh If set to true, the new object will be created (be careful, data
	 *                      in old object will be lost)
	 *
	 * @return \spaf\simputils\models\Box|array
	 */
	#[Shortcut('$_POST')]
	static function POST($refresh = false): Box|array {
		if (is_null(static::$_post) || $refresh) {
			static::$_post = static::bro($_POST);
		}
		return static::$_post;
	}

	/**
	 * Returns $_GET data wrapped into a Box
	 *
	 * Important: It's caching. Every call will return the same object
	 *
	 * It's name is beyond of the common convention of the library on purpose, to increase
	 * intuitive usage and distinct between "get" methods
	 *
	 * @param bool $refresh If set to true, the new object will be created (be careful, data
	 *                      in old object will be lost)
	 *
	 * @return \spaf\simputils\models\Box|array
	 */
	#[Shortcut('$_GET')]
	static function GET($refresh = false): Box|array {
		if (is_null(static::$_get) || $refresh) {
			static::$_get = static::bro($_GET);
		}
		return static::$_get;
	}

	public static function classShortName(string $val): string {
		$class_reflection = new ReflectionClass($val);
		return $class_reflection->getShortName();
	}

	static function objToNaiveString($self, $fields = null) {
		$class = static::classShortName($self::class);

		$res = "Object <{$class}#{$self->obj_id}";

		if (!empty($fields)) {
			$res .= ':';
			foreach ($fields as $k => $v) {
				$res .= " {$k}={$v}";
			}
		}
		$res .= '>';

		return $res;
	}

	/**
	 * Run code block in a transaction style like in python
	 *
	 * Resources are temporarily unsupported, it will be added later
	 *
	 * Keep in mind, if true is returned from the `___withStart()` method,
	 * it will prevent further execution of "callback". This is done, so
	 * you could execute the callback right inside of the `___withStart()`
	 * wrapped into a "try-catch". It's done exactly for the purpose of
	 * exception processing in case of need. Just don't forget to execute
	 * the callable int this case.
	 *
	 * Example:
	 * ```php
	 *  class Totoro extends SimpleObject {
	 *      protected function ___withStart($obj, $callback) {
	 *          pr('PREPARED! %)');
	 *          //		$callback($obj);
	 *          //		return true;
	 *      }
	 *
	 *      protected function ___withEnd($obj) {
	 *          pr('POST DONE %_%');
	 *      }
	 * }
	 *
	 * $obj = new Totoro;
	 *
	 *
	 * with($obj, function () {
	 *      pr('HEY! :)');
	 * });
	 *
	 * ```
	 *
	 * @param object|SimpleObject $obj      Object on which start and end
	 *                                      methods should be ran
	 * @param callable            $callback Code block that should be run in between
	 *
	 * @return void
	 */
	static function with($obj, callable $callback): void {
		if (static::classUsesTrait($obj, MetaMagic::class)) {
			$res = (bool) static::metaMagicSpell($obj, 'withStart', $obj, $callback);
		} else {
			$res = (bool) $obj->___withStart($obj, $callback);
		}

		if (!$res) {
			$callback($obj);
		}

		if (static::classUsesTrait($obj, MetaMagic::class)) {
			static::metaMagicSpell($obj, 'withEnd', $obj);
		} else {
			$obj->___withEnd($obj);
		}
	}
}
