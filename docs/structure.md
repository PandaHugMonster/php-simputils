[<< Back to README.md](../README.md)

----

# Structure


![Structure Schema](images/Structure%20Scheme%20v1.png)

**Legend**
 * Grey squares - Static classes
 * Blue squares - Model classes
 * Green squares - Prism classes
 * Yellow square - different ungrouped classes
 
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

More about static classes: [Static Classes](static-classes-group.md)

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
3. [Temperature](#Temperature) (code [\spaf\simputils\models\Temperature](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Temperature.php))
4. [BigNumber](#BigNumber) (code [\spaf\simputils\models\BigNumber](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/BigNumber.php))
5. [L10n](#L10n) (code [\spaf\simputils\models\L10n](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/L10n.php))
6. [SystemFingerprint](#SystemFingerprint) (code [\spaf\simputils\models\SystemFingerprint](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/SystemFingerprint.php))

#### Version

#### DataUnit

#### Temperature

#### BigNumber

#### L10n

#### SystemFingerprint

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
3. [GitRepo](#GitRepo) (code [\spaf\simputils\models\GitRepo](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/GitRepo.php))

#### File

#### Dir

#### GitRepo


-------

### Models of arrays and data-structures

1. [Box](#Box) (code [\spaf\simputils\models\Box](https://github.com/PandaHugMonster/php-simputils/blob/main/src/models/Box.php))
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

