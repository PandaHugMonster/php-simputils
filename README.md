:ukraine: #StandWithUkraine

This is the official Bank of Ukraine link for donations for Ukraine:

https://bank.gov.ua/en/news/all/natsionalniy-bank-vidkriv-spetsrahunok-dlya-zboru-koshtiv-na-potrebi-armiyi

-----
-----


# SimpUtils

Those badges are outdated for now :(

[![Build Status](https://app.travis-ci.com/PandaHugMonster/php-simputils.svg?branch=main)](https://app.travis-ci.com/PandaHugMonster/php-simputils)
[![codecov](https://codecov.io/gh/PandaHugMonster/php-simputils/branch/main/graph/badge.svg)](https://codecov.io/gh/PandaHugMonster/php-simputils)



**SimpUtils** is a micro-framework that provides really simple and lightweight tools
for development and prototyping. Additionally there are tools for comfortable and efficient
optimization and debugging.

The framework does not have much of composer dependencies (on purpose), to make sure that 
it does not bloat your `vendor` folder.

The main purpose of the framework is to improve development experience, and it does not oppose
any of the popular framework giants like "Yii2/3", "Laravel" or "Zend". The **SimpUtils**
can be easily used in combination of any framework.
For that matter I develop integration package of "SimpUtils" to "Yii2" framework:
https://github.com/PandaHugMonster/yii2-simputils

The framework extends PHP language with some useful perks. Provides similar to native classes,
but improves their capabilities. Normalizes naming and architecture.

All the aspects of the framework were designed to improve code development and readability.
All the components and features are designed to be intuitive and transparent for common use cases,
but really flexible in case of need.

P.S. The framework I develop for my own projects, but I'm really happy to share it with
anyone who is interested in it. Feel free to participate! Suggestions, bug-reports. 
I will be really happy hearing from you.

----

## Important notes
 1. Currently JSON serialization and deserialization does not work properly. 
    Please do not rely on it for now! **IMPORTANT!**
    When fix for this problem comes, and you are using current logic - you might get 
    into a broken code logic. Please do not use `\spaf\simputils\PHP::serialize()` and 
    `\spaf\simputils\PHP::deserialize()` code with JSON mechanics, you can switch the
    mechanics to native PHP like this (workaround):
    ```php
      PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_PHP;
      PHP::init();
    ```
    That will use native PHP mechanics for serialization, which should work properly 
    starting from this release (1.1.3)
 2. Starting from the release 1.1.6 fixed the bug with timezones indirect params (this
    partially changes the logic, but initial logic before that release was broken).

## Changelog

### 1.1.6

 * Implemented extensive PHPDOC with examples to `\spaf\simputils\basic` (in progress)
 * Fixed ticket #116 (Weird bug of "tz" on DateTime)
 * Fixed bug with incorrect interpretation of TZ parameter 
   in `\spaf\simputils\DT::normalize`. Previously `false` and `true` params for `$tz`
   were returning incorrect values.

[//]: # (FIX   Don't forget to implement proper tests for the fixed timezone machanics)

### 1.1.5

 * 

### 1.1.4

 * Fixed the ".env" autoload respect of the "working_dir" which was not working

### 1.1.3

 * Implemented method `\spaf\simputils\models\Box::batch()` that allows to easily export items
   of specified keys to the local variable scope
 * Implemented methods `setFromData()` and meta-magic methods `___serialize()` and 
   `___deserialize()` to fix PHP native serialization/deserialization for the 
   following classes:
   * `\spaf\simputils\models\Version`
   * `\spaf\simputils\models\UrlObject`
   * `\spaf\simputils\models\Time`
   * `\spaf\simputils\models\L10n`
   * `\spaf\simputils\models\IPv4Range`
   * `\spaf\simputils\models\IPv4`
   * `\spaf\simputils\models\File`
   * `\spaf\simputils\models\Dir`
   * `\spaf\simputils\models\DateTimeZone`
   * `\spaf\simputils\models\DateTime`
   * `\spaf\simputils\models\DatePeriod`
   * `\spaf\simputils\models\DateInterval`
   * `\spaf\simputils\models\Date`
   * `\spaf\simputils\models\DataUnit`
   * `\spaf\simputils\models\BigNumber`
 * Code Sniffer is removed from the project (got really annoyed, and it does not work correctly)
 * `\spaf\simputils\models\Time` and `\spaf\simputils\models\Date` have been refactored a bit.
   The caching mechanics has been fixed.
   * Additionally have been added the properties for `\spaf\simputils\models\Date`
     and `\spaf\simputils\models\Time` from the target `DateTime` object
   * `\spaf\simputils\models\Date` and `\spaf\simputils\models\Time` result of `for_system` 
     now returns the whole DateTime string value of UTC, not only the date or time component.
 * Implemented `\spaf\simputils\generic\BasicExecEnvHandler` Execution-Environment (aka stages),
   besides that implemented `\spaf\simputils\generic\BasicInitConfig::@$ee` property that 
   automatically will be assigned during `PHP::init()`, the object or params could be 
   adjusted in the incoming config, example:
   ```php
      $ic = PHP::init([
        'l10n' => 'AT',
        //	'ee' => new DummyExecEnvHandler(false, ee_name: 'TOO'),
        'ee' => [
            'ee' => 'test3-local',
            'is_hierarchical' => true,
            'permitted_values' => [
                'test1',
                'test2',
                'test3',
                'test4',
            ]
        ]
      ]);
      pd("{$ic->ee}", Boolean::to($ic->ee->is('test4-local')));
   ```
   For now not much of documentation is provided, but you always can define your own
   implementation of the class like `\spaf\simputils\components\execenvs\DummyExecEnvHandler`
   to handle your Exec-Env/stages implementation! More documentation and example will follow.
 * Additionally implemented `\spaf\simputils\components\execenvs\DummyExecEnvHandler`
   which is a dummy handler that just returns the predefined value. Should not be used 
   on production.
 * Implemented `\spaf\simputils\exceptions\ExecEnvException` exception for Exec-Env cases
 * Implemented `\spaf\simputils\models\Box::popFromStart()` and 
   `\spaf\simputils\models\Box::popFromEnd()` methods to get value from the box, return
   and remove it from the box.
 * Implemented tests for:
   * Exec-Env
   * Box batch functionality

### 1.1.2

 * Implemented `\spaf\simputils\basic\with` functionality of a transactional style like
   python `with` command. Really useful for DB and other connection types.

### 1.1.1

 * Implemented `\spaf\simputils\components\normalizers\BoxNormalizer` To normalize simple
   arrays when assigned to Properties

### 1.1.0

 * Implemented `FS::require()`, `FS::include()` and `FS::data()`
 * Implemented `PHP::listOfExecPhpFileExtensions()`, `PHP::listOfExecPhpMimeTypes()`
 * Now array/box argument for `File` constructor is allowed (like for `FS::locate()`)
 * Added support of `FS::locate()` alike array/box of path components for `fl()`, 
   `FS::file()` and `File`. So now `fl(['part1', 'part2', 'file.txt'])` will make a file
   object with path: "{working-dir}/part1/part2/file.txt"
 * In `BasicInitConfig` introduced component-aware `$allowed_data_dirs` for specifying
   allowed data-dirs
 * Introduced new exceptions: `DataDirectoryIsNotAllowed`, `IPParsingException`
 * Implemented the shortcut for the "InitConfig". Now instead of 
   `$config = PHP::getInitConfig()` you can use a shortcut `$config = ic()`
 * Fixed some of the logic related to "l10n" and "default_tz" more you can find here:
   [Nuances of l10n and default_tz](docs/notes.md#Nuances-of-l10n-and-default_tz)
 * Implemented list of **days of the week**: `\spaf\simputils\DT::getListOfDaysOfWeek()`
 * Implemented list of **months**: `\spaf\simputils\DT::getListOfMonths()`
 * Incorporated all the previous minor-version patches
   * To set the timezone for "DateTime" object now can be done by "strings" instead of
     creation of "DateTimeZone" object every single time
   * Other minimal changes
 * Implemented trait `\spaf\simputils\traits\ComparablesTrait` which enables to implement 
   common set of comparing functionality (`equalsTo`, `greaterThan`, `lessThan`, 
   `greaterThanEqual`, `lessThanEqual`) and their shortcuts (`e`, `gt`, `lt`, 
   `gte`, `lte`). Currently used in `Version` and `IPv4` models
 * Implemented `\spaf\simputils\models\IPv4` and `\spaf\simputils\models\IPv4Range` models
   with minimal but nice functionality
 * Implemented `\spaf\simputils\models\UrlObject` model 
   and `\spaf\simputils\models\urls\processors\HttpProtocolProcessor`
   * The most of the stuff should work out of the box except lack 
     of "to punycode" conversion. Cyrillic and other non-latin domains are 
     not converted to punycode.
 * Implemented `\spaf\simputils\System::localIp()` that gets the local IP
 * Implemented shortcuts `url()` for `\spaf\simputils\models\UrlObject` model and
   `ip()` for `\spaf\simputils\models\IPv4`
 * Added `\spaf\simputils\components\normalizers\IPNormalizer` property normalizer
 * Implementation of `\spaf\simputils\PHP::bro()` method (`\spaf\simputils\models\BoxRO`)
   which is basically "immutable Box"
 * Implemented shortcuts for getting `POST` and `GET` data as bros (BoxRO). Please keep 
   in mind that they are immutable due to best-practices:
   * `\spaf\simputils\PHP::POST()`
   * `\spaf\simputils\PHP::GET()`
 * Implemented `\spaf\simputils\PHP::objToNaiveString()` method to generate simple/naive
   object representation
 * Implemented some relevant tests
 * Important: Functionality of the Box is slightly extended. Now you can re-define static
   `\spaf\simputils\models\Box::$default_separator` variable value to string that should be used
   during `\spaf\simputils\models\Box::join()` and `\spaf\simputils\models\Box::implode()` as 
   a separator by default (initially default is ", " as it was before).
   Additionally you can specify `\spaf\simputils\models\Box::$separator` on per object basis
   that will be used in the object in case of "join" or "implode" without the first argument.
   That functionality allows to create "path-ready" Box-arrays, that can by default 
   be automatically converted into a "unix" path.
   `\spaf\simputils\models\Box::$joined_to_str` per object variable allows to define that
   this Box-object needs to be converted in `__toString()` method 
   through `\spaf\simputils\models\Box::join()` method, which is really useful for "path-ready"
   Box-arrays. See example here: [Path-alike Box-array](#Path-alike-Box-array)
 * For convenience create method-shortcut 
   to set Box as "Path-alike": `\spaf\simputils\models\Box::pathAlike()`
 * Added missing data-blocks for different locales


----

## Documentation

**Important note about the documentation**: Due to urgent need of the stable release, 
I had to strip out all the documentation (it was really outdated because of my architecture 
revisions). So please, wait just a bit, __with patches after the stable release I will provide
more documentation__. The very first stable release must be polished in matter of architecture,
so documentation will come after that in the very nearest time. My apologies.

### Some:
 1. [Glossary](docs/glossary.md)
 2. [Structure](docs/structure.md)
 3. [Important notes](docs/notes.md) - this can help with troubleshooting

----

## Installation

Minimal PHP version: **8.0**

Current framework version: **1.1.6**
```shell
composer require spaf/simputils "^1"
```

Keep in mind that the library development suppose to follow the semantic versioning,
so the functionality within the same major version - should be backward-compatible (Except 
cases of bugs and issues).

More about semantic versioning: [Semantic Versioning Explanation](https://semver.org).

## Quick highlights and examples

Just a few tini-tiny examples of very condensed functionality :)

1. [Properties](#Properties)
2. [Date Times](#Date-Times)
3. [Advanced PHP Info Object](#Advanced-PHP-Info-Object)
4. [IPv4 model](#IPv4-model)
5. [Path-alike Box-array](#Path-alike-Box-array)
5. ["with" love](#with-love)

### Properties

```php

use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
// Important to use exactly this DateTime class that is provided by the library
use spaf\simputils\models\DateTime;

require_once 'vendor/autoload.php';

/**
 * @property ?int $field_int
 * @property ?string $field_str
 * @property ?DateTime $field_dt
 */
class MyObjectOne extends SimpleObject {

	#[Property]
	protected ?int $_field_int = 22;

	#[Property(valid: 'upper')]
	protected ?string $_field_str = null;

	#[Property]
	protected ?DateTime $_field_dt = null;
}

// Always should be ran first in the runtime code
PHP::init();

////////////////////////

$m = new MyObjectOne;

pr($m); // Print out the whole content of the object

$m->field_int = 55.542;
$m->field_str = 'special string that is being normalized transparently';
$m->field_dt = '2000-11-20 01:23:45';

pd($m); // The same as pr(), but it dies after that

```

The output will be:
```php
MyObjectOne Object
(
    [field_dt] => 
    [field_int] => 22
    [field_str] => 
)

MyObjectOne Object
(
    [field_dt] => spaf\simputils\models\DateTime Object
        (
            [_simp_utils_property_batch_storage] => Array
                (
                )

            [_orig_value:protected] => 
            [date] => 2000-11-20 01:23:45.000000
            [timezone_type] => 3
            [timezone] => Europe/Berlin
        )

    [field_int] => 55
    [field_str] => SPECIAL STRING THAT IS BEING NORMALIZED TRANSPARENTLY
)
```

**Important**: Ignore some additional fields like "_orig_value" and 
"_simp_utils_property_batch_storage" in DateTime object, it's due to really nasty bug of 
the PHP engine, that seems to be really ignored by the PHP engine developers.

----

Cool way to hide some fields from the debug output with special attribute `DebugHide`:

```php

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
// Important to use exactly this DateTime class that is provided by the library
use spaf\simputils\models\DateTime;

require_once 'vendor/autoload.php';


/**
 * @property ?int $field_int
 * @property ?string $field_str
 * @property ?DateTime $field_dt
 * @property ?string $password
 */
class MyObjectOne extends SimpleObject {

	#[Property]
	protected ?int $_field_int = 22;

	#[Property(valid: 'upper')]
	protected ?string $_field_str = null;

	#[DebugHide]
	#[Property]
	protected ?DateTime $_field_dt = null;

	#[DebugHide(false)]
	#[Property(valid: 'lower')]
	protected ?string $_password = null;
}

// Always should be ran first in the runtime code
PHP::init();

////////////////////////

$m = new MyObjectOne;

pr($m); // Print out the whole content of the object

$m->field_int = 55.542;
$m->field_str = 'special string that is being normalized transparently';
$m->field_dt = '2000-11-20 01:23:45';
$m->password = 'MyVeRRy Secrete PaSWorT!@#';

pr($m);
pd("My password really is: {$m->password}");
```

And the output will be:
```php
MyObjectOne Object
(
    [field_int] => 22
    [field_str] => 
    [password] => ****
)

MyObjectOne Object
(
    [field_int] => 55
    [field_str] => SPECIAL STRING THAT IS BEING NORMALIZED TRANSPARENTLY
    [password] => ****
)

My password really is: myverry secrete paswort!@#
```

### Date Times

Simple quick iterations over the date period
```php

use spaf\simputils\PHP;
use spaf\simputils\models\DateTime;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\ts;

// Setting default user output format to Austrian
PHP::init([
	'l10n' => 'AT'
]);

foreach (DT::walk('2000-05-05', '2000-05-09', '12 hours') as $dt) {
	pr("$dt");
}

// Output would be:
//  05.05.2000 00:00
//  05.05.2000 12:00
//  06.05.2000 00:00
//  06.05.2000 12:00
//  07.05.2000 00:00
//  07.05.2000 12:00
//  08.05.2000 00:00
//  08.05.2000 12:00

// Changing locale settings for the user output to US format
$conf->l10n = 'US';

// Do the same iterations again, and output would be in US format
foreach (DT::walk('2000-05-05', '2000-05-09', '12 hours') as $dt) {
	pr("$dt");
}

// Output would be:
//  05/05/2000 12:00 AM
//  05/05/2000 12:00 PM
//  05/06/2000 12:00 AM
//  05/06/2000 12:00 PM
//  05/07/2000 12:00 AM
//  05/07/2000 12:00 PM
//  05/08/2000 12:00 AM
//  05/08/2000 12:00 PM


// The same can be achieved using directly DateTime without DT helper

$conf->l10n = 'RU';

// Both bellow are equivalents. Shortcut is a better choice
$obj1 = new DateTime('2001-05-17 15:00', 'UTC');
$obj2 = ts('2001-06-01 16:00');

foreach ($obj1->walk($obj2, '1 day') as $dt) {
	pr("$dt");
}

// Output would be something like:
//  17.05.2001 15:00
//  18.05.2001 15:00
//  19.05.2001 15:00
//  20.05.2001 15:00
//  21.05.2001 15:00
//  22.05.2001 15:00
//  23.05.2001 15:00
//  24.05.2001 15:00
//  25.05.2001 15:00
//  26.05.2001 15:00
//  27.05.2001 15:00
//  28.05.2001 15:00
//  29.05.2001 15:00
//  30.05.2001 15:00
//  31.05.2001 15:00
//  01.06.2001 15:00

```

Using prisms `Date` and `Time`:

```php

// Setting default user output format to Austrian
use function spaf\simputils\basic\ts;

PHP::init([
	'l10n' => 'AT'
]);

$dt = ts('2100-12-12 13:33:56.333');

echo "Date: {$dt->date}\n";
echo "Time: {$dt->time}\n";

// Output would be:
//  Date: 12.12.2100
//  Time: 13:33

// Chained object modification
echo "{$dt->date->add('22 days')->add('36 hours 7 minutes 1000 seconds 222033 microseconds 111 milliseconds')}\n";

// Output would be:
//  05.01.2101


// What is interesting, is that "date" prism just sub-supplying those "add()" methods to the
// target $dt object, so if we check now the complete condition of $dt it would contain all
// those modifications we did in chain above
echo "{$dt->format(DT::FMT_DATETIME_FULL)}";
// Would output:
//  2101-01-05 01:57:36.666033


// And Time prism would work the same way, but outputting only time part
echo "{$dt->time}\n";
// Output would be:
//  01:57

```

Both prisms of `Date` and `Time` work for any of the `DateTime` simputils objects.

All this math is done natively in PHP, so it's functionality native to PHP. The overall usage
was partially improved, so it could be chained and comfortably output.

All the object modification can be achieved through `modify()` method as well (`add()` and `sub()`
works in a very similar way).
Here you can read more about that: https://www.php.net/manual/en/datetime.modify.php

Examples of getting difference between 2 dates/times
```php

use function spaf\simputils\basic\ts;

$dt = ts('2020-01-09');

echo "{$dt->diff('2020-09-24')}\n";
// Output would be:
//  + 8 months 15 days


// You can use any date-time references
$obj2 = ts('2020-05-19'); 
echo "{$dt->diff($obj2)}\n";
// Output would be:
//  + 4 months 10 days

// Just another interesting chained difference calculation
echo "{$dt->add('10 years')->add('15 days 49 hours 666 microseconds')->diff('2022-01-29')}\n";
// Output would be:
//  - 7 years 11 months 28 days 1 hour 666 microseconds

```

What if you want to get difference for all those modifications? In general you could use
the following approach:
```php

use function spaf\simputils\basic\ts;

$dt = ts('2020-01-09');
$dt_backup = clone $dt;
$dt->add('10 years')->add('15 days 49 hours 666 microseconds')->sub('23 months');
echo "{$dt->diff($dt_backup)}\n";

// Output would be:
//  - 8 years 1 month 17 days 1 hour 666 microseconds
```

But example above is a bit chunky, it would be slightly more elegant to do the following:
```php
use function spaf\simputils\basic\ts;

$dt = ts('2020-01-09');
$dt->add('10 years')->add('15 days 49 hours 666 microseconds')->sub('23 months');
echo "{$dt->diff()}\n";
// Output would be:
//  - 8 years 1 month 17 days 1 hour 666 microseconds

// This way the diff will use for the first argument the initial value that was saved before first
// "modification" methods like "add()" or "sub()" or "modify()" was applied.

// Reminding, that any of the "modification" methods would cause modification of the target
// DateTime object
```

**Important:** All the changes are accumulative, because they call
`$this->snapshotOrigValue(false)` with `false` first argument. If you call that method
without argument or with `true` - it will override the condition with the current one.

Example of `DatePeriod`
```php

use spaf\simputils\PHP;
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\ts;


$conf = PHP::init([
	'l10n' => 'AT'
]);

$dp = ts('2020-01-01')->walk('2020-05-05', '1 day');

pr("$dp");

// Output would be:
//  01.01.2020 00:00 - 05.05.2020 00:00
```

Example of `DateInterval`

```php

use spaf\simputils\PHP;
use spaf\simputils\models\DateInterval;

$conf = PHP::init([
	'l10n' => 'AT'
]);

$di = DateInterval::createFromDateString('2 days');

pr("$di");
// Output would be:
//  + 2 days

```

Suggested to always use SimpUtils versions of DateTime related classes.

### Advanced PHP Info Object

Everything is really simple with it.
It's an array-like (box-like) object that contains almost complete PHP Info data.
That you can access and walk through any comfortable for you way. It also compatible
with the common IDE autocomplete (only top level fields).

You can access top-level fields (those that directly on the object):
1. In a property/field-like style:
   ```php
   use spaf\simputils\PHP;
   $phpi = PHP::info();
   echo "{$phpi->cpu_architecture}";
   ```
2. In an array-like style (box functionality is also available):
   ```php
   use spaf\simputils\PHP;
   $phpi = PHP::info();
   echo "{$phpi['cpu_architecture']}";
   ```
3. Iterate over the object:
   ```php
   use spaf\simputils\PHP;
   $i = 0;
   foreach (PHP::info() as $k => $v) {
       echo "{$k} ====> {$v}\n";
       if ($i++ > 4) {
           // Just a small limiter
           break;
       }
   }
   ```

## Additional benefits
1. All the versions are wrapped into `Version` class (out of the box version comparison, etc.)
2. The object is created once, and can be accessed through `PHP::info()`
   (manually possible to have multiple)
3. The object is being derivative from Box, that means that it has all the benefits (
   all the underlying arrays are Boxed as well, so the whole content of the php info
   is available through Box functionality)
4. Contains lots of information, and probably will be extended in the future with more
   relevant information.

## Reasoning to use Advanced PHP Info Object
The native `phpinfo()` returns just a static text representation, which is incredibly
uncomfortable to use.
Info about native one you can find here: https://www.php.net/manual/ru/function.phpinfo.php

### IPv4 model

Simple example:
```php

$ic = PHP::init([
	'l10n' => 'AT',
]);

/**
 * @property ?string $name
 * @property ?IPv4 $my_ip
 */
class Totoro extends SimpleObject {

	#[Property]
	protected ?string $_name = null;

	#[Property]
	protected ?IPv4 $_my_ip = null;

}

$t = new Totoro;

$t->name = 'Totoro';
$t->my_ip = '127.0.0.1/16';
$t->my_ip->output_with_mask = false;

pr("I am {$t->name} and my address is {$t->my_ip} (and ip-mask is {$t->my_ip->mask})");

```

The output would be:
```
I am Totoro and my address is 127.0.0.1 (and ip-mask is 255.255.0.0)
```

### Path-alike Box-array

This is a new feature for `Box` model/

```php
$b = new Box(['TEST', 'PATH', 'alike', 'box']);

pr("{$b}"); // In this case JSON

$b->joined_to_str = true;

pr("{$b}");

$b->separator = '/';

pr("{$b}");

$b->separator = ' ## ';

pr("{$b}");

```

The output would be:
```

["TEST","PATH","alike","box"]
TEST, PATH, alike, box
TEST/PATH/alike/box
TEST ## PATH ## alike ## box

```


### "with" love

Python specific command `with` can be easily implemented through meta-magic and callables.

Simple example:
```php

PHP::init();

class Totoro extends SimpleObject {

	protected function ___withStart($obj, $callback) {
		pr('PREPARED! %)');
//		$callback($obj);
//		return true;
	}

	protected function ___withEnd($obj) {
		pr('POST DONE %_%');
	}

}

$obj = new Totoro;

with($obj, function () {
	pr('HEY! :)');
});
```

You can access the target object easily from the callable:
```php
$obj = new Totoro;

with($obj, function ($obj) {
	pr('HEY! :)', $obj);
});

// or less elegant way:
with($obj, function () use ($obj) {
	pr('HEY! :)', $obj);
});

```

The example above can be combined if you want to use more from the outer scope,
but to keep the elegant way :)

```php
$obj = new Totoro;

$var1 = 1;
$var2 = 0.2;
$var3 = 'CooCoo';

with($obj, function ($obj) use ($var1, $var2, $var3) {
	pr('HEY! :)', $obj, $var1, $var2, $var3);
});

```

The syntax obviously is not that cute as in python, but functionally it's the same thing.

P.S. Keep in mind that the `with()` functionality relies on "MetaMagic" trait, and object
should use either the trait or implement 2 methods of `___withStart()` and `___withEnd()`


----

Really important to note - all above is really minimal description, 
basically a tip of the iceberg! There are lots of useful functionality description will be
added in upcoming weeks.

----
