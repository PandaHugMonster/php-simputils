# Changelog

## Latest
[//]: # (NOTE   Don't forget to update this file)

* Added `FS::join()` method that works similarly to python `os.path.join()` function
  * Improved `FS::glueFullFilePath()` method to use `FS::join()`, this should improve
    functionality on other platforms than UNIX/Linux
  * 


## 1.1.6

* Fixed issue with `DatePeriod` on PHP release 8.2
* Added procedure for running tests against minor and major releases of PHP

## 1.1.5

* Implemented extensive PHPDOC with examples to `\spaf\simputils\basic` (in progress)
* Fixed ticket #116 (Weird bug of "tz" on DateTime)
* Fixed bug with incorrect interpretation of TZ parameter
  in `\spaf\simputils\DT::normalize`. Previously `false` and `true` params for `$tz`
  were returning incorrect values.
* #144 | Added Renderer functionality
	* `\spaf\simputils\traits\StaticRendererTrait` - Trait for static helpers to enable rendering
	  features on them
	* `\spaf\simputils\traits\BaseHtmlTrait` - Minimal HTML related methods and renderers trait
	* `\spaf\simputils\Attrs` - PHP Attributes related helper
	* `\spaf\simputils\Html` - Minimal HTML helper that can be used as a base for custom HTML helper
    * You can try out a simple renderer:
      ```php
      use spaf\simputils\Html;
      use function spaf\simputils\basic\now;
      Html::render(now());
      ```
* Fixed issue with url "params" when the params with empty value are stripped out.
* Added models `\spaf\simputils\models\Password` and `\spaf\simputils\models\Secret`.
  Documentation: [Passwords and Secrets explained](passwords-and-secrets.md)
* Added some documentation and examples into [README.md](../README.md)
* Improved composer scripts for the framework development and analysis/testing
* Fixed some minimal amount of mess (Cyclomatic Complexity)
* Added `\spaf\simputils\PHP::currentUrl()` method
* Added `\spaf\simputils\attributes\DebugHide::$default_placeholder` field for default placeholder instead of `****`
  * Usage on secrets and passwords will cooperate with those objects in a better way, displaying proper "placeholder"
* Added `\spaf\simputils\components\normalizers\VersionNormalizer` normalizer
* Added Canadian locale `CA`
* Files now can be accessed through `with` functionality
  * Added `$in_memory_type` parameter for `File` constructor
  * Added integer support for `File` constructor to provide File Descriptor as integer instead of file path 
    (limited to runtime)
* Added a file processor for: `\spaf\simputils\models\files\apps\PHPFileProcessor` (basically prevent from displaying as text)
* Improved `\spaf\simputils\models\files\apps\CsvProcessor` and `\spaf\simputils\models\files\apps\settings\CsvSettings`
* Improved `Box::join()`/`Box::impload()` functionality
  * Added `apply()` method "params": `stretcher, value_wrap, key_wrap`
    * Added `paramsAlike()` (URL params) method
    * Added `htmlAttrAlike()` (HTML attributes) method
    * Added `stretched()` method
* Some improvements of Url Objects
* A bit more testing coverage is added
* Some more little stuff could be added or polished

## 1.1.4

* Fixed the ".env" autoload respect of the "working_dir" which was not working

## 1.1.3

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
	* Additionally, have been added the properties for `\spaf\simputils\models\Date`
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

## 1.1.2

* Implemented `\spaf\simputils\basic\with` functionality of a transactional style like
  python `with` command. Really useful for DB and other connection types.

## 1.1.1

* Implemented `\spaf\simputils\components\normalizers\BoxNormalizer` To normalize simple
  arrays when assigned to Properties

## 1.1.0

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
  [Nuances of l10n and default_tz](notes.md#Nuances-of-l10n-and-default_tz)
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
  Additionally, you can specify `\spaf\simputils\models\Box::$separator` on per object basis
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
