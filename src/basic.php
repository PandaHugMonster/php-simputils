<?php
// TODO @noinspection Generic.NamingConventions.CamelCapsFunctionName.NotCamelCaps
/**
 * Procedural shortcuts for functionality of different core classes
 * like `spaf\simputils\PHP`, `spaf\simputils\Str`, etc.
 */
namespace spaf\simputils\basic;

use DateTimeZone;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\files\File;
use spaf\simputils\PHP;

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
 * @param mixed ...$args Anything you want to print out before dying
 *
 * @see PHP::pd()
 * @see \die()
 * @see \print_r()
 */
function pd(...$args) {
	PHP::pd(...$args);
}

/**
 * @param ?array $array Array that should be wrapped into a box
 *
 * @return \spaf\simputils\models\Box|array
 */
function box(?array $array = null): Box|array {
	return PHP::box($array);
}

/**
 * Short and quick getting "now" `DateTime` object
 *
 * @param \DateTimeZone|null $tz TimeZone
 *
 * @return \spaf\simputils\models\DateTime|null
 *
 * @throws \Exception Parsing error
 */
function now(?DateTimeZone $tz = null): ?DateTime {
	return PHP::now($tz);
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
 * @throws \Exception Parsing error
 */
function ts(DateTime|string|int $dt, ?DateTimeZone $tz = null, string $fmt = null): ?DateTime {
	return PHP::ts($dt, $tz, $fmt);
}

function fl(null|string|File $file = null, $app = null): ?File {
	return PHP::file($file, $app);
}

/**
 * Getting Environment Variable value
 *
 * @param ?string $name   Variable name
 * @param bool    $strict Is strict mode (`$name` must be exactly as variable specified, otherwise
 *                        exception will be raised)
 *
 * @return mixed returns value of the environment variable or if no `name` is provided returns all
 *               the env variables.
 * @see PHP::allEnvs()
 */
function env(?string $name = null, bool $strict = true): mixed {
	if (empty($name)) {
		return PHP::allEnvs();
	}
	return PHP::env($name, $strict);
}

/**
 * @param string $name
 * @param mixed $value
 * @param bool $strict
 *
 * @see PHP::envSet()
 */
function env_set(string $name, mixed $value, bool $strict = true) {
	PHP::envSet($name, $value, $strict);
}
