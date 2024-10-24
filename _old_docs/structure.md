# Structure

[//]: # (FIX    Update and improve this page)

## Legend
* Grey squares - Static classes
* Blue squares - Model classes
* Green squares - [Prism](glossary.md#term-prism) classes
* Yellow square - different ungrouped classes
* Violet lines with a single arrow - class inheritance, **arrow points to a parent**
* Bright red lines with double arrows - means bidirectional co-operation, they might not
  be related in matter of OOP relations
* Sketched squares - not implemented yet, but most likely will be implemented
* Dashed, sharp-cornered with green, italic label - PHP Interfaces

![Structure Schema](images/Structure%20Scheme.png)

## Recommended usage structure

![Structure Schema](images/Usage%20structure%20of%20classes%20groups.png)

General suggestion is to use shortcut methods from "basic.php" first (like `fl()`, `bx()`, 
`ts()`, `now()`, etc.) to use models' related functionality (like `File`, `Box`, `DateTime`, etc.)

Those shortcuts should be really comfortable to use together with IDEs, but in case if it troubles
you that IDE does not recognize properly those shortcuts for some reason - you could use 
Static Classes of the corresponding functionality. Almost all the "basic" functions are using
relevant methods from static classes. So you can use those static class methods directly. 
This for sure will not mess with your IDEs auto-completion functionality, because those are
normal classes.

### Current basic functions and their static class equivalents

 1. `PHP` Static Class
    * `bx()` - `PHP::box()` (Creates advanced object-array, can be used instead of normal arrays)
    * `env()` - `PHP::env()` (Getting environmental value)
    * `pd()` - `PHP::pd()` (Prints out arguments in a debug mode and then dies)
    * `pr()` - `PHP::pr()` (Prints out arguments in a debug mode)
    * `prstr()` - `PHP::prstr()` (Prints out arguments into a string in a debug mode)
    * `stack()` - `PHP::stack()` (Creates Stack object)
 2. `FS` Static Class (file-system)
    * `dr()` - `FS::dir()` (Creates a directory object)
    * `fl()` - `FS::file()` (Creates a file object)
    * `path()` - `FS::path()` (Joins elements into a path string depending on the platform)
 3. `Data` Static Class
    * `du()` - `Data::du()` "du" in this context stands for "Data Unit" and **not** "Disk Usage"
      (Creates data-unit object)
 4. `DT` Static Class (date-time) 
    * `now()` - `DT::now()` (Creates DateTime object with the current time)
    * `ts()` - `DT::ts()` (Creates DateTime object with the specified date/time, in any format)

### Relation between static classes and models

![Structure Schema](images/Static%20classes%20relation%20with%20models.png)
 
## Overview
Overall there are 6 logical groups of functionality, at least major ones.

 1. [Static classes group](#Static-classes-group)
 2. [Models of measure and common purpose](#Models-of-measure-and-common-purpose)
 3. [Models of date and time](#Models-of-date-and-time)
 4. [Models of files and file-system](#Models-of-files-and-file-system)
 5. [Models of arrays and data-structures](#Models-of-arrays-and-data-structures)
 6. [Initialization and bootstrapping](#Initialization-and-bootstrapping)
 
---------

### Static classes group

**Static Classes** or **Helpers** both stands for the classes that are not supposed 
to be instantiated. They might be used as "Single Static Instance" when they store some
data inside of them, but their main functionality is to provide common methods.

Static Classes are not redefinable as a part of the framework. 
If for some reasons you would want to extend them - please create your own custom 
classes and inherit from those. This should not limit you in any sense.

Functionality in those classes is redistributed mostly in an intuitive way, so
`Math` helper would refer to mathematical functionality, and `FS` helper refers to
file-system functionality, and so on.

The only special case is `PHP` helper. It has some functionality that suppose to fix
flaws of PHP Engine, for example "serialization" and "deserialization" mechanics or
`PHP::type()` method that comfortably identifies the variable type.


 1. [Boolean](#boolean) (code [\spaf\simputils\Boolean](https://github.com/PandaHugMonster/php-simputils/blob/main/src/Boolean.php))
 2. [Data](#data) (code [\spaf\simputils\Data](https://github.com/PandaHugMonster/php-simputils/blob/main/src/Data.php))
 3. [DT](#dt) (code [\spaf\simputils\DT](https://github.com/PandaHugMonster/php-simputils/blob/main/src/DT.php))
 4. [FS](#fs) (code [\spaf\simputils\FS](https://github.com/PandaHugMonster/php-simputils/blob/main/src/FS.php))
 5. [Math](#math) (code [\spaf\simputils\Math](https://github.com/PandaHugMonster/php-simputils/blob/main/src/Math.php))
 6. [PHP](#php) (code [\spaf\simputils\PHP](https://github.com/PandaHugMonster/php-simputils/blob/main/src/PHP.php))
 7. [Str](#str) (code [\spaf\simputils\Str](https://github.com/PandaHugMonster/php-simputils/blob/main/src/Str.php))
 8. [System](#system) (code [\spaf\simputils\System](https://github.com/PandaHugMonster/php-simputils/blob/main/src/System.php))

#### Boolean

Static class `\spaf\simputils\Boolean` provides functions 
to work with boolean values (and theirs' variations)

For now it's not very developed, but it contains useful functionality (`Boolean::from()`) 
to convert other types and particular strings into boolean type value.
For example:

```php
use spaf\simputils\Boolean;

$bool_val = Boolean::from('yes');
// Would return TRUE value (of type boolean)

$bool_val = Boolean::from('NO');
// Would return FALSE value (of type boolean).
// The letter-case is irrelevant
```


#### Data

Static class `\spaf\simputils\Data` provides functions
to work with data-units.

#### DT

Static class `\spaf\simputils\DT` provides functions
to work with Date and Time.

#### FS

Static class `\spaf\simputils\FS` provides functions
to work with file system

#### Math

Static class `\spaf\simputils\Math` provides functions
to work with Math

#### PHP

Static class `\spaf\simputils\PHP` provides general framework
and PHP functionality

#### Str

Static class `\spaf\simputils\Str` provides functions
to work with strings

#### System

Static class `\spaf\simputils\System` provides functions
to obtain info about the system/platform

-------

### Models of measure and common purpose

1. [Version](#Version) (code [\spaf\simputils\models\Version](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Version.php))
2. [DataUnit](#DataUnit) (code [\spaf\simputils\models\DataUnit](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/DataUnit.php))
3. [BigNumber](#BigNumber) (code [\spaf\simputils\models\BigNumber](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/BigNumber.php))
4. [L10n](#L10n) (code [\spaf\simputils\models\L10n](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/L10n.php))


#### Version

#### DataUnit

#### BigNumber

#### L10n

-------

### Models of date and time

1. [DateTime](#DateTime) (code [\spaf\simputils\models\DateTime](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/DateTime.php))
2. [Date](#Date) (code [\spaf\simputils\models\Date](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Date.php))
3. [Time](#Time) (code [\spaf\simputils\models\Time](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Time.php))
4. [DateInterval](#DateInterval) (code [\spaf\simputils\models\DateInterval](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/DateInterval.php))
5. [DatePeriod](#DatePeriod) (code [\spaf\simputils\models\DatePeriod](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/DatePeriod.php))
6. [DateTimeZone](#DateTimeZone) (code [\spaf\simputils\models\DateTimeZone](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/DateTimeZone.php))

#### DateTime

#### Date

#### Time

#### DateInterval

#### DatePeriod

#### DateTimeZone


-------

### Models of files and file-system

1. [File](#File) (code [\spaf\simputils\models\File](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/File.php))
2. [Dir](#Dir) (code [\spaf\simputils\models\Dir](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Dir.php))

#### File

#### Dir


-------

### Models of arrays and data-structures

1. [Box](#Box) (code [\spaf\simputils\models\Box](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Box.php))
1. [Set](#Set) (code [\spaf\simputils\models\Set](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Set.php))
2. [StackLifo](#StackLifo) (code [\spaf\simputils\models\StackLifo](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/StackLifo.php))
3. [StackFifo](#StackFifo) (code [\spaf\simputils\models\StackFifo](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/StackFifo.php))
4. [PhpInfo](#PhpInfo) (code [\spaf\simputils\models\PhpInfo](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/PhpInfo.php))

#### Box

#### StackLifo

#### StackFifo

#### PhpInfo



-------

### Initialization and bootstrapping

1. [InitConfig](#InitConfig) (code [\spaf\simputils\models\InitConfig](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/InitConfig.php))

#### InitConfig

