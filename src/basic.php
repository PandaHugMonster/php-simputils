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
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\Box;
use spaf\simputils\models\DataUnit;
use spaf\simputils\models\DateInterval;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\IPv4;
use spaf\simputils\models\StackFifo;
use spaf\simputils\models\StackLifo;
use spaf\simputils\models\TimeDuration;
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
 * @return Box|array Ready to use Box-object that is being array-alike
 */
#[Shortcut('PHP::box()')]
function bx(mixed $array = null, mixed ...$merger): Box|array {
	return PHP::box($array, ...$merger);
}

/**
 * Create a stack object (FIFO and LIFO)
 *
 * Stack is a Box/Array object with pop/push functionality. Keys are not supported for now,
 * so please use indexed arrays.
 *
 * By default LIFO stack is created, if named parameter `type: 'fifo'` specified, then
 * FIFO stack is created.
 *
 * Simple example of mechanics:
 * ```php
 *  PHP::init([
 *      'l10n' => 'AT',
 *  ]);
 *
 *  $stack_lifo = stack(['test1', 'test2', 'test3']);
 *  $stack_fifo = stack(['test1', 'test2', 'test3'], type: 'fifo');
 *
 *  $str = 'My new value';
 *  $stack_lifo->append($str);
 *  $stack_fifo->append($str);
 *
 *
 *  $str = 'My last val';
 *  $stack_lifo->append($str);
 *  $stack_fifo->append($str);
 *
 *  pr($stack_lifo, $stack_fifo);
 *
 *  $popped_lifo_val = $stack_lifo->pop();
 *  $popped_fifo_val = $stack_fifo->pop();
 *
 *  pr("LIFO popped val: {$popped_lifo_val}");
 *  pr("FIFO popped val: {$popped_fifo_val}");
 *
 *  pr($stack_lifo, $stack_fifo);
 * ```
 *
 * Output:
 * ```
 *  spaf\simputils\models\StackLifo Object
 *  (
 *      [0] => test1
 *      [1] => test2
 *      [2] => test3
 *      [3] => My new value
 *      [4] => My last val
 *  )
 *
 *  spaf\simputils\models\StackFifo Object
 *  (
 *      [0] => test1
 *      [1] => test2
 *      [2] => test3
 *      [3] => My new value
 *      [4] => My last val
 *  )
 *
 *  LIFO popped val: My last val
 *  FIFO popped val: test1
 *  spaf\simputils\models\StackLifo Object
 *  (
 *      [0] => test1
 *      [1] => test2
 *      [2] => test3
 *      [3] => My new value
 *  )
 *
 *  spaf\simputils\models\StackFifo Object
 *  (
 *      [1] => test2
 *      [2] => test3
 *      [3] => My new value
 *      [4] => My last val
 *  )
 *
 * ```
 *
 * Functionality is pretty straight-forward:
 *  * **LIFO** - Last Input First Output
 *  * **FIFO** - First Input First Output
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
 * @return StackFifo|StackLifo Stack object, either LIFO or FIFO (Box-inherited)
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
 * Current time/now object of `DateTime`
 *
 * This method returns DateTime object of the current moment time.
 *
 * ```php
 *  PHP::init([
 *      'l10n' => 'AT',
 *  ]);
 *
 *  $now = now();
 *
 *  // You can use the object as string (user timezone and format)
 *  pr("This is string conversion of DateTime for user: {$now} ({$now->tz})");
 *  pr("This is string conversion of DateTime for DB: {$now->for_system} (UTC)");
 *
 *  // Or as object
 *  pr("DateTime object: ", $now);
 *
 *  $now->tz = 'America/Toronto';
 *  pr("This is string conversion of DateTime for user: {$now} ({$now->tz})");
 *
 *  pr("DateTime object in new time-zone: ", $now);
 *
 * ```
 *
 * Output:
 * ```
 *  This is string conversion of DateTime for user: 08.08.2022 20:30 (Europe/Vienna)
 *  This is string conversion of DateTime for DB: 2022-08-08 18:30:58.673357 (UTC)
 *  DateTime object:
 *  spaf\simputils\models\DateTime Object
 *  (
 *      [_simp_utils_property_batch_storage] => Array
 *      (
 *      )
 *
 *      [_orig_value:protected] =>
 *      [date] => 2022-08-08 20:30:58.673357
 *      [timezone_type] => 3
 *      [timezone] => Europe/Vienna
 *  )
 *
 *  This is string conversion of DateTime for user: 08.08.2022 14:30 (America/Toronto)
 *  DateTime object in new time-zone:
 *  spaf\simputils\models\DateTime Object
 *  (
 *      [_simp_utils_property_batch_storage] => Array
 *      (
 *      )
 *
 *      [_orig_value:protected] =>
 *      [date] => 2022-08-08 14:30:58.673357
 *      [timezone_type] => 3
 *      [timezone] => America/Toronto
 *  )
 * ```
 *
 * Keep in mind that timezone for "user" format is specified globally in `PHP::init()`.
 *
 * In this case used default Austrian timezone. But separately could be specified globally
 * different timezone from l10n/locale value:
 *
 * ```php
 *
 *  PHP::init([
 *      'l10n' => 'AT',
 *      'default_tz' => 'Asia/Novosibirsk'
 *  ]);
 *
 *  $now = now();
 *
 *  pr("{$now} ({$now->tz})");
 * ```
 *
 * Output:
 * ```
 *  09.08.2022 01:38 (Asia/Novosibirsk)
 * ```
 *
 * Locally on the object timezone easily could be changed by assigning
 * value to `tz` property.
 *
 * ```php
 *  PHP::init([
 *      'l10n' => 'AT',
 *  ]);
 *
 *  $now = now();
 *
 *  $now->tz = 'Europe/Kiev';
 *
 *  pd("{$now} ({$now->tz})");
 * ```
 *
 * Output:
 * ```
 *  08.08.2022 21:48 (Europe/Kiev)
 * ```
 *
 * @see \spaf\simputils\basic\ts() Gives DateTime object for the specified time
 *
 * @param DateTimeZone|bool|string|null $tz TimeZone
 *
 * @return ?DateTime Current date-time in form of `DateTime` object
 */
#[Shortcut('DT::now()')]
function now(DateTimeZone|bool|string|null $tz = null): ?DateTime {
	return DT::now($tz);
}

/**
 * Short and quick getting `DateTime` object of specified date and time
 *
 * All the examples form {@see \spaf\simputils\basic\now()} are applicable (and vice-versa),
 * because both functions return the same data-type object
 * `\spaf\simputils\models\DateTime` (NOT PHP NATIVE `\DateTime` OBJECT!)
 *
 * Function is idempotent.
 *
 * #### There are a few ways to specify the datetime data, and meaning of it.
 *
 *
 * The datetime data could be specified in the following formats/data-types:
 *  * `\spaf\simputils\models\DateTime` object - in this case it will be immediately
 *    returned without any modifications.
 *  * **String** - the datetime can be specified in form of the string, any parsable
 *    native PHP format for specifying datetime is supported, example
 *    the most common/suggested: "2022-08-09 20:15:02". This datetime string will
 *    be parsed and `\spaf\simputils\models\DateTime` object returned
 *  * **Integer** - basically a timestamp, amount of seconds that have elapsed since
 *    January 1st 1970, 00:00:00 UTC. Specified data in this format does not support
 *    microseconds.
 *
 * In the most cases "string" is preferred format to store and use, or the object itself.
 *
 * When "string" format is used for datetime, other parameters such as `$tz`, etc. start playing
 * their roles. `$tz` in this case can be specified in the following formats:
 *
 * 1. `$tz` is not specified or null - in this case the input datetime string will be
 *    parsed in "UTC" timezone but the returning DateTime object will be switched to the
 *    `$default_tz` (user's default) timezone. This way the value can be supplied right from a
 *    DB or storage in "for_system" format. And the final object will be displayed for user
 *    in the correct time-offset.
 * 2. `$tz` is specified to `true` - in this case datetime string will be parsed in
 *    the `$default_tz` (user's default) timezone and DateTime object will be
 *    returned with the same `$default_tz` (user's default) timezone.
 * 3. `$tz` is specified to `false` - in this case datetime string will be parsed in
 *    'UTC' timezone and DateTime object will be returned with the same 'UTC' timezone.
 * 4. `$tz` as non-empty string with the value from the native PHP list of timezones
 *    {@see https://www.php.net/manual/en/timezones.php}. This timezone will be used for both
 *    parsing the datetime string and for returned DateTime object.
 * 5. `$tz` as a `\DateTimeZone` or `\spaf\simputils\models\DateTimeZone` object.
 *    This timezone will be used for both parsing the string and the returned DateTime object.
 *
 * Maybe this amount of the documentation about Timezones topic is an overkill, but this
 * topic is the least understood and the most underestimated across the globe.
 *
 * So all the above looks difficult, but in fact is not that difficult. When you will
 * understand the reasoning for those complexities, you might agree that they are necessary.
 *
 * The framework has generally a concept of 2 outputting states like `for_user` and `for_system`.
 * Some objects, and especially DateTime object has 2 fields with very the same names.
 * This duality suppose to decouple purposes of "displaying for user" and "storing in
 * the storage" (DB, Caching, Serialization, etc.)
 *
 * And that's basically it, `for_user` is used to display the value in the most readable
 * for a user format (even loss of some portion of data during displaying might be
 * totally fine), when the string returned by the `for_system` field must be totally lossless,
 * and contain the full representation of the data (Full datetime format in UTC, json,
 * or whatever, etc.).
 *
 * For the case of DateTime object `for_system` **always has to return maximum of the info
 * in the same parsable format always and only in UTC timezone.**
 * Like this: `2020-12-24 03:16:05.123456`
 *
 * So in this case it's easy to understand now, than if you process this `for_system` value
 * of the DateTime object through `ts('2020-12-24 03:16:05.123456')` you will get 2 good
 * things from it:
 * 1. It will be converted into a DateTime object which you can use as "string" for user
 * 2. It will create object with a true UTC value, and adjust it's user output
 *    to a proper timezoned value.
 *
 * This is why it's so twisted and weird with DateTime stuff.
 *
 * #### Short recap:
 *  * Incoming string in UTC timezone, out-coming object is in "Default TZ" timezone
 *    ```php
 *      $dt = ts('2020-12-24 03:16:05.123456');
 *    ```
 *  * Incoming string in UTC timezone, out-coming object is in UTC timezone
 *    ```php
 *      $dt = ts('2020-12-24 03:16:05.123456', false);
 *    ```
 *  * Incoming string in "Default TZ" timezone, out-coming object is
 *    in "Default TZ" timezone
 *    ```php
 *      $dt = ts('2020-12-24 03:16:05.123456', true);
 *    ```
 *  * Incoming string in **Europe/Vienna** timezone, out-coming object is
 *    in **Europe/Vienna** timezone
 *    ```php
 *      $dt = ts('2020-12-24 03:16:05.123456', 'Europe/Vienna');
 *    ```
 *
 * All the 4 cases above allows you to keep control over how to generate ready to use
 * DateTime object.
 *
 * @todo Add more documentation and examples here with DateTime object
 *
 * @see \spaf\simputils\basic\now() Gives current time object (now)
 *
 * @param DateTime|string|int           $dt  Any date-time representation (DateTime object,
 *                                           string, int)
 * @param null|bool|DateTimeZone|string $tz  TimeZone
 * @param string|null                   $fmt FROM Format, usually not needed, just if you are using
 *                                           a special date-time format to parse
 *
 * @return ?DateTime Specified date-time in form of `DateTime` object
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

#[Shortcut('DT::duration()')]
function dur(int|float|DateInterval $value = 0): TimeDuration {
	return DT::duration($value);
}

/**
 * Creating File object
 *
 * @param string|resource|int|Box|array|File|null $file
 * @param $app
 *
 * @return File|null
 */
#[Shortcut('FS::file()')]
function fl(mixed $file = null, $app = null): ?File {
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
	null|UrlCompatible|string|Box|array $host = null,
	null|Box|array|string $path = null,
	null|Box|array $params = null,
	?string $protocol = null,
	?string $processor = null,
	?string $port = null,
	?string $user = null,
	?string $pass = null,
	mixed ...$data,
): UrlObject {
	return PHP::url($host, $path, $params, $protocol, $processor, $port, $user, $pass, ...$data);
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
#[Shortcut('PHP::with()')]
function with($obj, callable $callback): void {
	PHP::with($obj, $callback);
}
