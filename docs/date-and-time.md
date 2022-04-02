[<< Back to README.md](../README.md)

----

# Date and Time

1. [Relevant components]()
2. [Cool math and perks for Date and Time]() (Useful examples are also here)
3. [Date and Time System output and User output]()
4. [DateTime model]()
   1. [Related shortcuts]()
   2. [What differs from the native class]()
   3. [Some examples]()

Work with Date and Time was always a nightmare in PHP (and in other languages as well).

So this is a set of functionality suppose to improve overall satisfaction operating
with Date and Time.

## Relevant components
1. `\spaf\simputils\models\DateTime` - Extended version of native PHP `\DateTime` some
   more details here: [DateTime model]()
2. `\spaf\simputils\models\DateTimeZone` - Extended version of native PHP `\DateTimeZone`
3. `\spaf\simputils\models\DateInterval` - Extended version of native PHP `\DateInterval`
4. `\spaf\simputils\models\DatePeriod` - Extended version of native PHP `\DatePeriod`
5. `\spaf\simputils\models\Date` - Date **Prism** for `DateTime`
6. `\spaf\simputils\models\Time` - Time **Prism** for `DateTime`
7. `\spaf\simputils\DT` - A static helper to work with Date and Time
8. `\spaf\simputils\basic\now` - A shortcut to `\spaf\simputils\DT::now()`
9. `\spaf\simputils\basic\ts` - A shortcut to `\spaf\simputils\DT::ts()`

In the most cases you just need shortcuts `now()` and `ts()` to work with date and time

## Cool math and perks for Date and Time

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
$obj1 = new DateTime('2001-05-17 15:00');
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


## Date and Time System output and User output

Worth mentioning that there are as minimum 2 important "output modes"
* **User output** - this is an output in country/locale-wise format with TimeZones applied
* **System output** - this is an output for storing and in-code usage, it's always in the
  same format, and it's always in **UTC**.

Frameworks best-practices suggest usage of __absolute "UTC" format for storing and using
in the code__, while the User's format always output's in the comfortable locale-aware
format.

**Important:** The direct conversion to string like this `echo "{$dt}";` would always use
**User output** format

To be able explicitly control the output format, there are 2 special properties called:
1. [**System output**] `$dt->for_system` property returns the absolute UTC formatted
   string commonly used in a code and DBs
2. [**User output**] `$dt->for_user` property returns time-zoned locale-aware format for
   the user (It's equivalent of direct string conversion like: `"$dt"`)

`Date` and `Time` prisms have the same properties and they return in a proper format for their
purpose.

## DateTime model

Extended version of the original native PHP `DateTime` class.
Info and api-ref about native PHP class you can
find here: https://www.php.net/manual/ru/class.datetime.php

### Related shortcuts

More info about shortcuts here: [Shortcuts](shortcuts.md)

* [ts()](shortcuts.md#ts) shortcut that returns `DateTime` object of specified
  datetime value
* [now()](shortcuts.md#now) shortcut that returns `DateTime` object of a current moment

### What differs from the native class
* [Redefinable component](redefinable-components.md)
* All the benefits of `SimpleObject` inherited (through `SimpleObjectTrait`)
    * Easy "to string" casting
    * [Properties](attributes/Property.md)
* Added properties (for comfortable day-to-day use):
    * `$dt->date`   Date part string representation
    * `$dt->time`   Time part string representation
    * `$dt->tz`     [DateTimeZone](DateTimeZone.md) (extended version as well) object
    * `$dt->week`   Week number of the year
    * `$dt->doy`    Day of the year
    * `$dt->year`   Year value
    * `$dt->month`  Month value
    * `$dt->day`    Day value
    * `$dt->dow`    Day Of Week
    * `$dt->hour`   Hours value
    * `$dt->minute` Minutes value
    * `$dt->second` Seconds value
    * `$dt->milli`  Milliseconds value
    * `$dt->micro`  Microseconds value (including milliseconds part)


### Some examples

Simple usage:
```php

use spaf\simputils\PHP;
use function spaf\simputils\basic\ts;

PHP::init();

////

$dt = ts('2022-07-18 13:19:04');
echo "DateTime string: {$dt}, it is year {$dt->year} and week number {$dt->week}";
// Output:
//  "DateTime string: 2022-07-18 13:19:04.000000, it is year 2022 and week number 29"

```

More examples:
```php

use spaf\simputils\PHP;
use function spaf\simputils\basic\now;

PHP::init();

////

echo "Orig:\t {$dt->date}\n";
$dt->year = 2030;
echo "New:\t {$dt->date}\n";
// Output:
//  Orig:	 2022-01-05
//  New:	 2030-01-05

echo "The time zone is: {$dt->tz}\n";
// Output:
//  "The time zone is: Europe/Berlin"

print_r($dt->tz);
// Output:
//  spaf\simputils\models\DateTimeZone Object
//  (
//      [timezone_type] => 3
//      [timezone] => Europe/Berlin
//  )

print_r($dt);
// Output:
//  spaf\simputils\models\DateTime Object
//  (
//      [date] => 2030-01-05 09:58:40.428276
//      [timezone_type] => 3
//      [timezone] => Europe/Berlin
//  )

```


Example of setting up locale and timezones:

```php

use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\PHP;

class SpecInitBlock extends SimpleObject implements InitBlockInterface {

	public function initBlock(BasicInitConfig $config): bool {
		$config->l10n = 'RU';
		// $config->default_tz = 'Asia/Novosibirsk';

		return true;
	}
}

PHP::init([ new SpecInitBlock() ]);

$d = ts('2022-05-05 12:44');
//$d = now();

pd("USER:\t{$d->for_user} ({$d->tz})", "SYSTEM:\t{$d->for_system} (UTC)");

// Would output something like:
//  USER:	05.05.2022 15:44 (Europe/Moscow)
//  SYSTEM:	2022-05-05 12:44:00.000000 (UTC)

// If you uncomment "$config->default_tz = 'Asia/Novosibirsk'"
// Then output would be something like:
//  USER:	05.05.2022 19:44 (Asia/Novosibirsk)
//  SYSTEM:	2022-05-05 12:44:00.000000 (UTC)

```

Very cool and reasonable part that you can specify any locale + any timezone, 
because users of their own country can travel to other timezones without loosing their 
locale to a local one.

```php

use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\PHP;

class SpecInitBlock extends SimpleObject implements InitBlockInterface {

	public function initBlock(BasicInitConfig $config): bool {
		$config->l10n = 'AT';
		$config->default_tz = 'America/New_York';

		return true;
	}
}

PHP::init([ new SpecInitBlock() ]);

$d = ts('2022-02-13 12:44');
//$d = now();

pd("USER:\t{$d->for_user} ({$d->tz})", "SYSTEM:\t{$d->for_system} (UTC)");

// Would output something like:
//  USER:	13.02.2022 08:44 (America/New_York)
//  SYSTEM:	2022-02-13 12:44:00.000000 (UTC)

// When you would use l10n = "US" would output something like this:
//  USER:	02/13/2022 07:44 AM (America/New_York)
//  SYSTEM:	2022-02-13 12:44:00.000000 (UTC)

```
