# Working with Date/Time

* PHP time zones: https://www.php.net/manual/en/timezones.europe.php
* PHP formatting parameters: https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters

## Date/Time

### Simple use

Framework provides `\spaf\simputils\models\DateTime` class which could be used directly.
Though it's highly suggested to use a shortcut `ts()`
or at least through `\spaf\simputils\DT` helper `DT::ts()`.

```php

use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\ts;
use spaf\simputils\PHP;
use spaf\simputils\models\InitConfig;

/** @var InitConfig $ic */
$ic = PHP::init();

// The value passed in string as UTC by default
$dt = ts("2024-07-12 13:00:15");

// Timestamp is always considered UTC
pr("Timestamp: {$dt->timestamp}");

// The date/time is output in local Timezone
pr("Date: {$dt->date}");
pr("Time: {$dt->time}");

// By default, timezone is detected automatically from system
pr("Timezone: {$dt->tz}");
```

Output:
```text
Timestamp: 1720789215
Date: 2024-07-12
Time: 15:00:15
Timezone: Europe/Berlin
```

If you need to get current time, you can use `now()` shortcut:
```php

use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pr;
use spaf\simputils\PHP;
use spaf\simputils\models\InitConfig;

/** @var InitConfig $ic */
$ic = PHP::init();

$dt = now();

// Timestamp is always considered UTC
pr("Timestamp: {$dt->timestamp}");

// The date/time is output in local Timezone
pr("Date: {$dt->date}");
pr("Time: {$dt->time}");

// By default, timezone is detected automatically from system
pr("Timezone: {$dt->tz}");
```

Output:
```text
Timestamp: 1720801684
Date: 2024-07-12
Time: 18:28:04
Timezone: Europe/Berlin
```

### Time zones
The major idea of the framework's date-time functionality is to reduce frictions
in working with timezones and UTC.

The concept here is that there are 2 sides:
1. Internal (in-code, runtime) use of date-time happens in UTC always.
2. User-facing representation of time is always time-zoned.

So any code-related interactions, calculations or storing to DB would be in UTC,
but when it suppose to be displayed to a user - it would be time-zoned.

```php
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\ts;
use spaf\simputils\PHP;
use spaf\simputils\models\InitConfig;

/** @var InitConfig $ic */
$ic = PHP::init();

$dt = ts("2024-07-12 13:00:00");

pr("UTC: \t\t{$dt->for_system}");
pr("Timezoned: \t{$dt->for_user} ({$dt->tz})");
```

Output:
```text
UTC: 		2024-07-12 13:00:00.000000
Timezoned: 	2024-07-12 15:00:00 (Europe/Berlin)
```

There are a few ways to change timezone on date-time objects:
1. Globally specifying `l10n` with 2-letter country code on init-config,
   it will assign the default timezone for the country.
   ```php
   use spaf\simputils\PHP;
   use function spaf\simputils\basic\pr;
   use function spaf\simputils\basic\ts;
   use spaf\simputils\models\InitConfig;
   
   /** @var InitConfig $ic */
   $ic = PHP::init([
       "l10n" => "CA",
   ]);
    
   $dt = ts("2024-07-12 13:00:00");
    
   pr("UTC: \t\t{$dt->for_system}");
   pr("Timezoned: \t{$dt->for_user} ({$dt->tz})");
   ```
   Output:
   ```text
   UTC: 		2024-07-12 13:00:00.000000
   Timezoned: 	2024-07-12 09:00 (America/Toronto)
   ```
2. Globally specifying `default_tz` on init-config,
   it will affect any newly created `DateTime` objects (has precedence over `l10n`).
   ```php
   use spaf\simputils\PHP;
   use function spaf\simputils\basic\pr;
   use function spaf\simputils\basic\ts;
   use spaf\simputils\models\InitConfig;
   
   /** @var InitConfig $ic */
   $ic = PHP::init([
       "l10n" => "CA",
       "default_tz" => "Europe/Madrid"
   ]);
    
   $dt = ts("2024-07-12 13:00:00");
    
   pr("UTC: \t\t{$dt->for_system}");
   pr("Timezoned: \t{$dt->for_user} ({$dt->tz})");
   ```
   Output:
   ```text
   UTC: 		2024-07-12 13:00:00.000000
   Timezoned: 	2024-07-12 15:00 (Europe/Madrid)
   ```
3. Locally specifying timezone after object creation:
   ```php
   
   use spaf\simputils\models\InitConfig;
   use spaf\simputils\PHP;
   use function spaf\simputils\basic\pr;
   use function spaf\simputils\basic\ts;
   
   /** @var InitConfig $ic */
   $ic = PHP::init([
   "default_tz" => "America/Toronto",
   ]);
   
   pr("Default TZ:\t{$ic->default_tz}");
   
   $dt = ts("2024-07-12 13:00:00");
   $dt->tz = "Europe/Vienna";
   
   pr("UTC: \t\t{$dt->for_system}");
   pr("Timezoned: \t{$dt->for_user} ({$dt->tz})");
   ```
   Output:
   ```text
   Default TZ:	America/Toronto
   UTC: 		2024-07-12 13:00:00.000000
   Timezoned: 	2024-07-12 15:00:00 (Europe/Vienna)
   ```
   > [!IMPORTANT]
   > Do not specify timezone on the object creation functions `ts()` or `now()`, it has different meaning
   > than you probably expect. It will be explained further 
   > in the section [Explaining `ts()` and `now()`](#explaining-ts-and-now).


### Explaining `ts()` and `now()`

1. `ts()` - create date-time object with a specified value
2. `now()` - create date-time object with a current date-time value

#### Specific timezone

Signatures of both functions has `tz` parameter, which specifies **incoming** and **outgoing** timezone.

The function `now()` does not have incoming value, so this `tz` parameter just going to represent
the final object's timezone.

```php
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pr;

/** @var InitConfig $ic */
$ic = PHP::init();

$dt = now(tz: "Europe/Rome");

pr("UTC: \t\t{$dt->for_system}");
pr("Timezoned: \t{$dt->for_user} ({$dt->tz})");
```

Output:
```text
UTC: 		2024-07-13 08:37:21.526915
Timezoned: 	2024-07-13 10:37:21 (Europe/Rome)
```

The function `ts()` does have incoming value, and if it's a `string`, the timezone will be used for parsing
it in the specified timezone, and representing final object's timezone.

> [!NOTE]
> Very important to note that the specified string value will be considered and parsed 
> as the value in this timezone, so the `UTC` will be adjusted according to that.
> 
> This functionality is mainly intended for parsing the user's input. 
> **Try to avoid using it for any other purpose**.

```php

use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\ts;

require_once "vendor/autoload.php";

/** @var InitConfig $ic */
$ic = PHP::init();

$dt = ts("2024-07-13 13:15:30.012345", tz: "Europe/Rome");

pr("UTC: \t\t{$dt->for_system}");
pr("Timezoned: \t{$dt->for_user} ({$dt->tz})");
```

Output:
```text
UTC: 		2024-07-13 11:15:30.012345
Timezoned: 	2024-07-13 13:15:30 (Europe/Rome)
```

As you can see, the specified value is parsed in `Europe/Rome` timezone, and `UTC` representation is adjusted
accordingly (-2 hours).

The major thing about framework's `DateTime` object, is that it operates in `UTC`. 
It is strongly recommended that any date-time logic you are keeping in mind should be relative to `UTC`, 
and timezone is just an addition to that, not vice-versa.

Never work with date-time relative to certain timezone, it's a recipe for disaster.

#### Parsing strings

With the parameter `fmt` of `ts()` you can specify unconventional parsing format for the date-time string:
```php
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\ts;

require_once "vendor/autoload.php";

/** @var InitConfig $ic */
$ic = PHP::init();
$dt = ts("13 15 30 012345 / 7 2024 13", fmt: "H i s u / m Y d");

pr("UTC: \t\t{$dt->for_system}");
pr("Timezoned: \t{$dt->for_user} ({$dt->tz})");
```

Output:
```text
UTC: 		2024-07-13 13:15:30.012345
Timezoned: 	2024-07-13 15:15:30 (Europe/Berlin)
```

PHP formatting parameters: https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters

#### String representations of `DateTime` objects

There are 2 major ways to represent `DateTime` objects as strings

1. `for_system` property - converts `UTC` representation of `DateTime` object to string in the default format `Y-m-d H:i:s.u`
2. `for_user` property - converts timezoned representation of `DateTime` object to string in the locale-specific format.

As it is implied by the namings, `for_system` should be used code, storage, db-wise.
When the `for_user` should be used for outputting the value to the user of your software.

When the whole object is stringified like this:

```php
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;
use function spaf\simputils\basic\pr;
use function spaf\simputils\basic\ts;

/** @var InitConfig $ic */
$ic = PHP::init();

$dt = ts("2024-07-12 13:00:00");

pr("For user: \t{$dt}");
```

Output:
```text
For user: 	2024-07-12 15:00:00
```

It's basically a shortcut for `for_user` property (especially convenient for embedding into HTML).

> [!IMPORTANT]
> Code, storage, db-wise always explicit `for_system` must be used!


## Date/Time atoms


## Time zones

