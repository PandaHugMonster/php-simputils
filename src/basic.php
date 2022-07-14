<?php
/**
 * Procedural shortcuts for functionality of different core classes
 * like `spaf\simputils\PHP`, `spaf\simputils\Str`, etc.
 */
namespace spaf\simputils\basic;

use DateTimeZone;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\Data;
use spaf\simputils\DT;
use spaf\simputils\FS;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\BasicIP;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\Box;
use spaf\simputils\models\DataUnit;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\IPv4;
use spaf\simputils\models\StackFifo;
use spaf\simputils\models\StackLifo;
use spaf\simputils\models\UrlObject;
use spaf\simputils\PHP;

/**
 * Please Die function
 *
 * Print out all the supplied params, and then die/exit the runtime.
 * Basically, could be considered as a shortcut of sequence of "print_r + die"
 *
 * Die/Exit functionality can be disabled and enabled explicitly (by default it's enabled).
 * This can be done globally through setting `PHP::$allow_dying` boolean static var.
 * Additionally, if somebody redefined "pd" callback, the return of this callback
 * can control "die/exit" action (If the callback does not return anything or return FALSE
 * equivalent then "die/exit" will be disabled dynamically. In case if it returns TRUE
 * equivalent then "die/exit" will be enabled dynamically).
 *
 * ## Usage examples:
 *
 * ```php
 *  PHP::init();
 *  pd('Proverka', 12, 45.55, ['here', 'it', 'goes'], bx(['test1', 'test2']));
 *  // After this point nothing will be executed (unless die was explicitly disabled)
 * ```
 *
 * The output:
 * ```
 *  Proverka
 *  12
 *  45.55
 *  Array
 *  (
 *      [0] => here
 *      [1] => it
 *      [2] => goes
 *  )
 *
 *  spaf\simputils\models\Box Object
 *  (
 *      [0] => test1
 *      [1] => test2
 *  )
 * ```
 *
 * To override the `pd()` with own callback:
 * ```php
 *  PHP::init([
 *      'redefinitions' => [
 *          InitConfig::REDEF_PD => function (...$args) {
 *              $args = bx($args);
 *
 *              echo "Ho Ho Ho / {$args} / Gooli Gooli Gooli";
 *
 *              // Return nothing or false to dynamically disable "die" part
 *              return true;
 *          }
 *      ]
 *  ]);
 *
 *
 *  pd('test1', 'test2', 'test3', now());
 *  // At this point nothing will be executed, because callback is returning TRUE.
 * ```
 *
 * The output:
 * ```
 * Ho Ho Ho / ["test1","test2","test3","2022-07-04 04:31:12.510651"] / Gooli Gooli Gooli
 * ```
 *
 * @param mixed ...$args Anything you want to print out before dying
 * @return never|void In the most cases it will die before returning anything,
 *                    but keep in mind that the dying functionality might be disabled
 *                    in some cases as described above
 *
 * @see PHP::pd() The base method that contains logic
 * @see PHP::pr() The output is based on this method
 * @see \die()    Used in the most cases to exit the app
 */
#[Shortcut('PHP::pd()')]
function pd(mixed ...$args) {
	PHP::pd(...$args);
}

/**
 * Create Box (array-alike object)
 *
 * Box is one of the Keystones of the framework.
 *
 * With simple words - they are improved version of PHP native arrays, because
 * they are being objects that work exactly like array. Generally there should not be
 * any performance penalty on those, but just remember that they are
 * chunkier than arrays (insignificantly).
 *
 * There are 3 important things to mention:
 * 1. All the provided params should be wrapped into a native PHP array with "[" and "]".
 * 2. Besides providing just one argument (array with data), you can provide multiple arguments.
 *    In this case all the arguments of the function will be merged. All the later values will
 *    have precedence over values earlier. Index arrays are not merged, but replaced.
 *    This merger functionality is not "deep-merge" one.
 * 3. Function is idempotent. **Important**: Supplying `Box` objects instead of arrays is exactly
 *    the same as with arrays. The only difference, the very first argument (if it's a box)
 *    will be used as a return value (the returned value will be EXACTLY the same object as
 *    was on the argument part, it will be just extended with new values).
 *
 * ## Usage examples:
 * ```php
 *  PHP::init();
 *
 *  // Empty Box
 *  $a = bx();
 *
 *  // Box with a few values
 *  $b = bx(['value1', 'value2', 2, 0.9]);
 *
 *  // Box with a few values and assoc indexes
 *  $c = bx([
 *      'abc' => 'value1',
 *      'value2',
 *      'def' => 2,
 *      0.9
 *  ]);
 *
 *  // Box from merging
 *  $d = bx($c->clone(), ['def' => 'NOPE', 4 => 'NEW VALUE'], [4 => 999]);
 *  // Cloning here is done on purpose to avoid modification of "$c" object
 *
 *  // "pr" or "pd" to show the content
 *  pr($a, $b, $c, $d);
 * ```
 *
 * Output:
 * ```
 *  spaf\simputils\models\Box Object
 *  (
 *  )
 *
 *  spaf\simputils\models\Box Object
 *  (
 *      [0] => value1
 *      [1] => value2
 *      [2] => 2
 *      [3] => 0.9
 *  )
 *
 *  spaf\simputils\models\Box Object
 *  (
 *      [abc] => value1
 *      [0] => value2
 *      [def] => 2
 *      [1] => 0.9
 *  )
 *
 *  spaf\simputils\models\Box Object
 *  (
 *      [abc] => value1
 *      [0] => value2
 *      [def] => NOPE
 *      [1] => 0.9
 *      [4] => NEW VALUE
 *      [5] => 999
 *  )
 * ```
 *
 * **Important**:
 * You should not rely on the numerical indexes during merging.
 * The "integer" indexes are not fully respected.
 * This is why "999" value with initial "4" index was placed with the index "5".
 * The numerical indexes are always added, if the target array does not
 * have such numeric key then it will be added with this exact key,
 * if the key like that exists in the target array, it will not replace it, but add
 * the value to the end of the array with serial numerical index
 *
 * To access values simply use array style:
 * ```php
 *
 *  PHP::init();
 *
 *  $b = bx(['value 1', 'key1' => 'value 2', 3.33, 4 => 4.4444]);
 *
 *  echo "The first value is \"{$b[0]}\". \nBut second value is \"{$b[1]}\"," .
 *       " when the third value is \"{$b[4]}\" and has index 4. \n" .
 *       "The only missing is assoc value of key1 and it contains \"{$b['key1']}\"\n";
 * ```
 *
 * Output:
 * ```
 *  The first value is "value 1".
 *  But second value is "3.33", when the third value is "4.4444" and has index 4.
 *  The only missing is assoc value of key1 and it contains "value 2"
 * ```
 *
 * Beside that, you could request value that might be missing in the Box, and receive
 * fallback value in this case (which by default is `null`)
 *
 * Example:
 * ```php
 *
 *  PHP::init();
 *
 *  $b = bx(['value 1', 'key1' => 'value 2', 3.33, 4 => 4.4444]);
 *
 *  echo "This is non-existing in the box value: {$b->get('PANDA', 'I am a fallback')}";
 *
 * ```
 *
 * The output:
 * ```
 *  This is non-existing in the box value: I am a fallback
 * ```
 *
 * The `Box` object can be easily inserted into a string with a few different ways:
 * ```php
 *  PHP::init();
 *
 *  $b = bx(['my', 'special', 'array', 'values', 15]);
 *
 *  pr("Box is stringified as json: {$b}");
 *
 *  $b->joined_to_str = true;
 *
 *  pr("Box is stringified by joining: {$b}");
 *
 *  $b->separator = "/";
 *
 *  pr("Box is stringified and pretending a path: {$b}");
 *
 *  $b->separator = "\\";
 *
 *  pr("Box is stringified and pretending another type of path: {$b}");
 *
 *  $b->separator = " ## ";
 *
 *  pr("Box is stringified with custom block: {$b}");
 *
 * ```
 *
 * Output:
 * ```
 *  Box is stringified as json: ["my","special","array","values","15"]
 *  Box is stringified by joining: my, special, array, values, 15
 *  Box is stringified and pretending a path: my/special/array/values/15
 *  Box is stringified and pretending another type of path: my\special\array\values\15
 *  Box is stringified with custom block: my ## special ## array ## values ## 15
 * ```
 *
 * In the example above we were changing aspects of just a single object.
 * But the same way you could adjust behaviour of all the Boxes at once:
 *
 * ```php
 *  PHP::init();
 *
 *  Box::$default_separator = ' || ';
 *  Box::$is_joined_to_str = true;
 *
 *  $b = bx(['my', 'special', 'array', 'values', 15]);
 *
 *  pr("Joined string: {$b}");
 * ```
 *
 *
 * For path-alike cases there is a nice method that automatically uses the slashes
 * on per object cases:
 * ```php
 *
 *  PHP::init();
 *
 *  $b = bx(['my', 'special', 'array', 'values', 15]);
 *
 *  $b->pathAlike();
 *
 *  pr("Path-alike: {$b}");
 *
 *  // Or different path separator
 *  $b->pathAlike('\\');
 *
 *  pr("Different path-alike: {$b}");
 * ```
 *
 * Output:
 * ```php
 *  Path-alike: my/special/array/values/15
 *  Different path-alike: my\special\array\values\15
 * ```
 *
 * Normal iterations through `foreach` can be done exactly like with native arrays:
 * ```php
 *  $b = bx(['value 1', 'key1' => 'value 2', 3.33, 4 => 4.4444]);
 *  foreach ($b as $key => $val) {
 *      pr("Iteration of {$key} and it's value \"{$val}\"");
 *  }
 * ```
 *
 * Output:
 * ```
 *  Iteration of 0 and it's value "value 1"
 *  Iteration of key1 and it's value "value 2"
 *  Iteration of 1 and it's value "3.33"
 *  Iteration of 4 and it's value "4.4444"
 * ```
 *
 *
 * @param null|Box|array $array     Array that should be wrapped into a box
 * @param mixed          ...$merger Arrays/Boxes to merge into first one
 *
 * @return Box|array
 */
#[Shortcut('PHP::box()')]
function bx(mixed $array = null, mixed ...$merger): Box|array {
	return PHP::box($array, ...$merger);
}

/**
 * Create a stack object
 *
 *
 * @param Box|StackLifo|StackFifo|array|null $items              Items
 * @param mixed                              ...$merger_and_conf All the items that should be pushed
 *                                                               into the newly created
 *                                                               stack object. Must not have "keys"
 * @param string                             $type               This key should be explicitly
 *                                                               specified.
 *                                                               Should contain "fifo" or "lifo",
 *                                                               by default is "lifo".
 *
 * @return \spaf\simputils\models\StackFifo|\spaf\simputils\models\StackLifo
 * @noinspection PhpDocSignatureInspection
 */
#[Shortcut('PHP::stack()')]
function stack(
	Box|StackLifo|StackFifo|array|null $items = null,
	mixed ...$merger_and_conf
): StackFifo|StackLifo {
	return PHP::stack($items, ...$merger_and_conf);
}

/**
 * Short and quick getting "now" `DateTime` object
 *
 * @param DateTimeZone|bool|string|null $tz TimeZone
 *
 * @return DateTime|null
 */
#[Shortcut('DT::now()')]
function now(DateTimeZone|bool|string|null $tz = null): ?DateTime {
	return DT::now($tz);
}

/**
 * Short and quick getting `DateTime` object of specified date and time
 *
 * @param DateTime|string|int $dt  Any date-time representation (DateTime object, string, int)
 * @param \DateTimeZone|null  $tz  TimeZone
 * @param string|null         $fmt FROM Format, usually not needed, just if you are using
 *                                 a special date-time format to parse
 *
 * @return DateTime|null
 *
 */
#[Shortcut('DT::ts()')]
function ts(
	DateTime|string|int $dt,
	null|bool|DateTimeZone|string $tz = null,
	string $fmt = null
): ?DateTime {
	return DT::ts($dt, $tz, $fmt);
}

/**
 * Creating File object
 *
 * @param string|Box|array|File|null $file
 * @param $app
 *
 * @return File|null
 */
#[Shortcut('FS::file()')]
function fl(null|string|Box|array|File $file = null, $app = null): ?File {
	return FS::file($file, $app);
}

/**
 * Creating Dir object
 *
 * @param string|null $path
 *
 * @return \spaf\simputils\models\Dir|null
 */
#[Shortcut('FS::dir()')]
function dr(null|string $path = null): ?Dir {
	return FS::dir($path);
}

/**
 * Getting Environment Variable value
 *
 * @param ?string $name    Variable name
 * @param mixed   $default Default value if env variable does not exist
 *
 * @return mixed returns value of the environment variable or if no `name` is provided returns all
 *               the env variables.
 *
 * @see PHP::allEnvs()
 * @see PHP::envSet()
 */
#[Shortcut('PHP::env()')]
function env(?string $name = null, mixed $default = null): mixed {
	return PHP::env($name, $default);
}

/**
 * Print any amount and any kind of objects/variables in detailed format
 *
 * @param ...$args
 *
 * @return void
 * @see PHP::pd()
 * @see \print_r()
 */
#[Shortcut('PHP::pr()')]
function pr(...$args): void {
	PHP::pr(...$args);
}

#[Shortcut('PHP::prstr()')]
function prstr(...$args): ?string {
	return PHP::prstr(...$args);
}

#[Shortcut('PHP::path()')]
function path(?string ...$args): ?string {
	return FS::path(...$args);
}

/**
 * Creating DataUnit object
 *
 * @param int|string|DataUnit|null $value  Data unit in any format
 * @param string|null              $format Data Unit object default format (if not set, then HR
 *                                         is used)
 *
 * @return \spaf\simputils\models\DataUnit
 * @throws \spaf\simputils\exceptions\RedefUnimplemented Redefinable component is not defined
 */
#[Shortcut('Data::du()')]
function du(null|int|string|DataUnit $value = null, ?string $format = null): DataUnit {
	return Data::du($value, $format);
}

/**
 * Returns InitConfig object
 *
 * @param string|null $name Name of the init config. If empty, used the main "app" InitConfig
 *
 * @return \spaf\simputils\models\InitConfig|\spaf\simputils\generic\BasicInitConfig|null
 */
#[Shortcut('PHP::ic()')]
function ic(?string $name = null): null|InitConfig|BasicInitConfig {
	return PHP::ic($name);
}

/**
 * Creating URL object
 *
 * @param UrlObject|UrlCompatible|string|Box|array|null $host
 * @param Box|array|string|null $path
 * @param Box|array|null $params
 * @param string|null $protocol
 * @param mixed ...$data
 *
 * @return \spaf\simputils\models\UrlObject
 */
#[Shortcut('PHP::url()')]
function url(
	UrlObject|UrlCompatible|string|Box|array $host = null,
	Box|array|string $path = null,
	Box|array $params = null,
	string $protocol = null,
	mixed ...$data,
): UrlObject {
	return PHP::url($host, $path, $params, $protocol, ...$data);
}

/**
 * Creating IP object
 *
 * @param string|BasicIP $ip
 *
 * @return IPv4
 */
#[Shortcut('PHP::ip()')]
function ip(string|BasicIP $ip): IPv4 {
	return PHP::ip($ip);
}
