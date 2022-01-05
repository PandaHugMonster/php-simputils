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


### Examples

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
