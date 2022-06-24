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
#[Shortcut('PHP::pd()')]
function pd(mixed ...$args) {
	PHP::pd(...$args);
}

/**
 * Create Box array-like object
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

#[Shortcut('FS::file()')]
function fl(null|string|Box|array|File $file = null, $app = null): ?File {
	return FS::file($file, $app);
}

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
 * DataUnit shortcut
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
 * Shortcut for InitConfig object
 *
 * @param string|null $name Name of the init config. If empty, used the main "app" InitConfig
 *
 * @return \spaf\simputils\models\InitConfig|\spaf\simputils\generic\BasicInitConfig|null
 */
#[Shortcut('PHP::ic()')]
function ic(?string $name = null): null|InitConfig|BasicInitConfig {
	return PHP::ic($name);
}

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

#[Shortcut('PHP::ip()')]
function ip(string|BasicIP $ip): IPv4 {
	return PHP::ip($ip);
}
