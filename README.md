:ukraine: #StandWithUkraine

This is the official Bank of Ukraine link for donations for Ukraine:

https://bank.gov.ua/en/news/all/natsionalniy-bank-vidkriv-spetsrahunok-dlya-zboru-koshtiv-na-potrebi-armiyi

-----

-----

# SimpUtils

## Description

**SimpUtils** is a micro-framework that provides really simple and lightweight tools
for development and prototyping. Additionally, there are tools for comfortable and efficient
optimization and debugging.

The framework does not have much of composer dependencies (on purpose), to make sure that
it does not bloat your `vendor` folder.

The main purpose of the framework is to improve development experience, and it does not oppose
any of the popular framework giants like "Yii2/3", "Laravel" or "Zend". The **SimpUtils**
can be easily used in combination of any framework.


The framework extends PHP language with some useful perks. Provides similar to native classes,
but improves their capabilities. Normalizes naming and architecture.

All the aspects of the framework were designed to improve code development and readability.
All the components and features are designed to be intuitive and transparent for common use cases,
but really flexible in case of need (Version `1.*.*` has some architectural flaws though,
those will be eliminated from version `2.0.0`).

P.S. The framework I develop for my own projects, but I'm really happy to share it with
anyone who is interested in it. Feel free to participate! Suggestions, bug-reports.
I will be really happy hearing from you.



## Info

 * Minimal PHP version: **8.0**
 * Current framework version: **1.1.6**
 * License: [MIT](LICENSE)
   * Authors of this framework are not liable for any problems/damages related to usage 
     of this framework. Use it on your own risk!
 * Examples and Usage:
   1. [Quick Start](#Quick-Start)
      1. [Installation](#installation)
      2. [Minimal usage](#Minimal-usage)
   2. Features:
      1. [Markers](docs/markers.md)
      2. [Renderers](docs/features/renderers.md)
      3. [Working with URLs](docs/features/urls.md)
      4. [Files, Data Files and DotEnv](docs/features/files.md)
      5. [Properties](#Properties)
      6. [Date Times](#Date-Times)
      7. [Advanced PHP Info Object](#Advanced-PHP-Info-Object)
      8. [IPv4 model](#IPv4-model)
      9. [Path-alike Box-array](#Path-alike-Box-array)
      10. [Stretchable feature of Box-array](#Stretchable-feature-of-Box-array) (`paramsAlike()`)
      11. ["with" love](#with-love)
      12. [Passwords and Secrets explained](docs/passwords-and-secrets.md)
   3. [Changelog](docs/changelog.md)
   4. [Glossary](docs/glossary.md)
   5. [Structure](docs/structure.md)
   6. [Important notes](docs/notes.md) - this can help with troubleshooting


-----

## Quick Start

### Installation

```shell
composer require spaf/simputils "^1"
```

Keep in mind that the library development suppose to follow the semantic versioning,
so the functionality within the same major version - should be backward-compatible (Except
cases of bugs and some rare issues).

More about semantic versioning: [Semantic Versioning](https://semver.org).

Unstable: [Unstable Versions Installation](docs/unstable-installation.md)

### Minimal usage

Despite the fact that it's suggested to run `PHP::init()` method before your code base,
you can use some features out of the box even without doing so.

It's just recommended to initialize framework before usage (some significant portion
of the functionality might rely on the initialization process).

```php

use spaf\simputils\PHP;

require_once 'vendor/autoload.php';

PHP::init();

// Here goes your code

```

It's very important to make sure that you include the composer `vendor/autoload.php` file
before usage of the framework.

-----


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

Keep in mind that for passwords and tokens/secrets 
there exists additional functionality: [Passwords and Secrets explained](docs/passwords-and-secrets.md)

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

#### Additional benefits

1. All the versions are wrapped into `Version` class (out of the box version comparison, etc.)
2. The object is created once, and can be accessed through `PHP::info()`
   (manually possible to have multiple)
3. The object is being derivative from Box, that means that it has all the benefits (
   all the underlying arrays are Boxed as well, so the whole content of the php info
   is available through Box functionality)
4. Contains lots of information, and probably will be extended in the future with more
   relevant information.

#### Reasoning to use Advanced PHP Info Object

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

This is a new feature for `Box` model.

The new short version `Box::pathAlike()` method is available:

```php
PHP::init();

$bx = bx(['TEST', 'PATH', 'alike', 'box'])->pathAlike();

pd($bx, "{$bx}");
```

Output would be:

```text
spaf\simputils\models\Box Object
(
    [0] => TEST
    [1] => PATH
    [2] => alike
    [3] => box
)

TEST/PATH/alike/box
```

Here is the manual way with different examples:

```php
PHP::init();

$b = bx(['TEST', 'PATH', 'alike', 'box']);

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

### Stretchable feature of Box-array

It works almost exactly as "Path-Alike", but it stringifies boxes including "keys".

Example 1:

```php

PHP::init();

$bx = bx([
    'key1' => 'val1',
    'key2' => 'val2',
    'key3' => 'val3',
    'key4' => 'val4',
])->stretched('=');

pd($bx, "{$bx}");

```

Output would be:

```text
spaf\simputils\models\Box Object
(
    [key1] => val1
    [key2] => val2
    [key3] => val3
    [key4] => val4
)

key1=val1, key2=val2, key3=val3, key4=val4
```

And as it might be obvious already, there is a really good potential to use it
for url params.

Example 2:

```php
PHP::init();

$bx = bx([
	'key1' => 'val1',
	'key2' => 'val2',
	'key3' => 'val3',
	'key4' => 'val4',
])->stretched('=', '&');

// or shorter and more intuitive:

$bx = bx([
	'key1' => 'val1',
	'key2' => 'val2',
	'key3' => 'val3',
	'key4' => 'val4',
])->paramsAlike();

pd($bx, "{$bx}");

```

Output would be:

```text
spaf\simputils\models\Box Object
(
    [key1] => val1
    [key2] => val2
    [key3] => val3
    [key4] => val4
)

key1=val1&key2=val2&key3=val3&key4=val4
```

Important to note, this methods does not turn the objects directly to strings!
They store in the object special configuration, that when you start
to stringify this Box - it will use the saved settings for that.

#### Value wrappers and htmlAttrAlike()

For html attrs alike just use this method:

```php
$bx = bx([
	'data-my-attr-1' => 'test',
	'data-my-attr-2' => 'test2',
])->htmlAttrAlike();
// You can specify first argument " or ' to control which wrapper symbols are used.
// Or you could even specify callable to pre-process and wrap value automatically!
```

Output would be:

```text
data-my-attr-1="test" data-my-attr-2="test2"
```

But if you would want to do "value-processing" instead of just wrapping, you could use
stretched functionality:

```php
$bx = bx([
	'data-my-attr-1' => 'test',
	'data-my-attr-2' => 'test2',
])->stretched(' = ', ' ', function ($val, $key, $bx) {
	return "(`{$val}`)";
});
```

Output would be:

```text
data-my-attr-1 = (`test`) data-my-attr-2 = (`test2`)
```

#### Wrap, wrap, wrap

For stretching functionality you can wrap each part separately
with `$value_wrap` and `$key_wrap`. They work in the same way, but wrap their each
corresponding part. After that or instead of that if for the `$stretcher` argument
provided the function/callable/closure then it will be used for wrapping the whole pair.

Keep in mind, that if you specify wrappers for `key` or `value` they already will
be applied before the `stretcher` callable is called!

Example bellow will help to understand the logic.

```php
$bx = bx([
	'key1' => 'val1',
	'key2' => 'val2',
	'key3' => 'val3',
])->stretched(fn($v, $k) => "(\"{$k}\": \"{$v}\")",  ' || ', '!', '?');

pd("$bx");
```

Output would be:

```text
("?key1?": "!val1!") || ("?key2?": "!val2!") || ("?key3?": "!val3!")
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

## Additional composer scripts
You can test and analyze code with some additional scripts in composer.

List all available scripts:
```shell
composer run -l
```

Output something like:
```text
scripts:
  test             Run the whole PHPUnit test suit                                                                                              
  coverage         Run the whole PHPUnit test with Coverage suit. Output in HTML at "docs/coverage/html"                                        
  coverage-clover  Run the whole PHPUnit test with Coverage suit. Output in clover xml at "docs/coverage/"                                      
  mess             Runs the mess script as defined in composer.json.                                                                            
  pipeline-mess    Runs phpmd Mess Analysis on some scopes and return non 0 exit status if rules are violated. Reasonable for CI/CD pipelines.
```

### Automated Testing and Coverage
#### Testing
Requires `PHPUnit`, `php-mbstring`, `php-xdebug`, `php-bcmath` (GMP extension will not work. It has loss of precision, 
so some tests will fail)

For APT-GET compatible OS those could be installed like this:
```shell
sudo apt install php-mbstring php-xdebug php-bcmath
```

Running tests
```shell
composer run test
```

#### Code Coverage
Requires `PHPUnit`

```shell
composer run coverage
```

----

### Mess Analysis
Requires `phpmd`

```shell
composer run mess
```
