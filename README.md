# SimpUtils

Minimum required PHP version is **8.0**

Micro-framework extending PHP language with some useful perks, partly can even remind 
python 3 development capabilities.

This lib (and related other libs) I develop the mostly for myself, but you are absolutely
welcome to use it/those for your own good. Feel free to propose updates and creating
issues, bugfixes and stuff!

At this context the words "lib" and "framework" both refers to the same meaning 
of "micro-framework".

**Important:** The code is partly unfinished. If you are interested in the lib and it's 
functionality - please wait until the stable release of **1.0.0** 
(currently it's **0.2.3**). 
Starting from that major version version, existing architecture most likely will 
not change for existing components (at least until the next major version change).

More about semantic versioning: [Semantic Versioning Explanation](https://semver.org).

## Installation

Install it through composer:
```shell
composer require spaf/php-simputils "*"
```


## Ground Reasons and Design Decisions

I love PHP, but some of the architectural decisions of it are "a bit" weird. 
From which I would highlight at least those (but not limited to):
 * Naming convention is not persistent even inside of the same section
   (See `Math` class)
 * Poor namespacing of the vital functionality which makes it look like a soup 
   (See `Math` class)
 * Lack of functional and comfortable basic instances like files and stuff
   (See `File` and `DateTime` (not PHP version, but library one) classes)
 * Outdated and too random way to create "Properties" from methods of a class
   (See `Property` and `PropertyBatch` attribute classes)
 * Lack of transparent conversion between types. For example try to `echo ['my', 'array]`
   (See `Box` class)
 * Lack of easy to use DotEnv (and auto-loading) and Env Vars
   (See `File` class)
 * Lack of replaceable components
 * ETC. (Lot's of other reasons behind)

Basically **SimpUtils** provides interconnected, consistent (more or less) tools for you to
code and prototype easily.

One of the coolest part of the framework - you don't need to use those components or 
functionality that you don't want. Just use those that you are interested in.

## Main Components

_Hint: to understand all the benefits of components - see examples_

### Core Static Classes and Functions

 1. `\spaf\simputils\PHP` static class provides some key functionality "PHP"-wise 
    and quick methods.
 2. `\spaf\simputils\Math` static class of math functionality. The mostly
    contains shortcuts of the PHP-native functions for math. Can be considered like 
    a namespaced default math-functionality. BUT, besides that it contains additional 
    functionality that is not present in the native PHP, 
    for example `\spaf\simputils\Math::divmod()`.
 3. `\spaf\simputils\Str` static class of simple strings-related functionalities
 4. `\spaf\simputils\Boolean` static class of simple bool-related functionalities
 5. `\spaf\simputils\FS` static class of simple file-related functionalities
 6. `\spaf\simputils\Data` static class to convert data units (bytes to kb, etc.)
 7. `\spaf\simputils\DT` static class providing functionality for date and time
 8. `\spaf\simputils\System` static class providing access to platform/system info
 9. `\spaf\simputils\basic` set of namespaced functions, really relevant for the most of
     situations. Really commonly used really often. Those functions in the most cases
     are being just shortcuts for Core Static Classes methods

### Core Models

 1. `\spaf\simputils\models\Box` - model class as a wrapper for primitive arrays 
 2. `\spaf\simputils\models\DateTime` - model for datetime value
 3. `\spaf\simputils\models\File` - model for file value
 4. `\spaf\simputils\models\GitRepo` - model representing minimal git functionality 
    (through shell commands)
 5. `\spaf\simputils\models\InitConfig` - Config for initialization process (bootstrapping,
    components redefinition and other stuff)
 6. `\spaf\simputils\models\PhpInfo` - really advanced version of `phpinfo()` in form of
    iterable object. Contains almost all of the relevant data from `phpinfo()` 
    but in parsed and extended state (for examples version info is wrapped into `Version`
    objects). May be extended even further, so will provide much more benefit, than
    clumsy native `phpinfo()`
 7. `\spaf\simputils\models\Version` - represents (and parses/generate) version value
 8. `\spaf\simputils\models\SystemFingerprint` - represents fingerprint of the system/data


### Core Attributes

 1. `\spaf\simputils\attributes\Property` used for marking methods to behave like 
    Properties
 2. `\spaf\simputils\attributes\PropertyBatch` similar to `Property`, but allows 
    to specify Properties in a batch mode
 3. `\spaf\simputils\attributes\markers\Shortcut` marking attribute to indicate method
    or function as a "Shortcut" to another functionality/variable
 4. `\spaf\simputils\attributes\markers\Deprecated` marking attribute to indicate anything
    as a deprecated element
 5. `\spaf\simputils\attributes\markers\Affecting` - should not be used. Unfinished concept

**Really quick reasoning:** You might ask why do we need `Deprecated` attribute, when we 
have JetBrains' (PHPStorm) composer dependency for similar attributes. And the answer 
would be: I really adore and love JetBrains and all of their products, but I can not let
to have additional composer dependency just for a few attributes.

## Other Components

_will be a added later_

## Examples

_In this section will be shown examples and benefits of the architecture_

**Important:** Not all the benefits and useful perks might be demonstrated on this page.
Please refer to the corresponding page of each component, or Ref API pages.

### Working with files

File content of `my-file.csv`:
```csv
col1,col2,col3,col4
cell1,cell2,cell3,12.3
"CELL 5","CELL 6","CELL 7",20
```

Code working with the file:
```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\fl;

// Framework init (recommended, but not mandatory)
PHP::init();

// The quickest access to content
echo fl('my-file.csv')->content;
// Output: [
//  {"col1":"cell1","col2":"cell2","col3":"cell3","col4":"12.3"},
//  {"col1":"CELL 5","col2":"CELL 6","col3":"CELL 7","col4":"20"}
// ]

// For some of the file types data automatically is parsed as shown above
// What is interesting, that `->content` returns data as "Box-array", what automatically
// printed out by "echo" as a "json" content.
// If it would be just a PHP "array", echo would cause Exception. It's one of the benefits
// of using `Box`s instead of PHP "arrays".

$file = fl('my-file.csv');
// $file will store an object now of type `File`

// If you would print it out or turn it to a string like this:
echo $file;
// Would output: ./my-file.csv

// It outputs file-name when converted to a string

// Now, let's try to view some info about the file:

echo "Size: {$file->size_hr} ({$file->size} bytes)\n";
echo "File name: {$file->name}, File extension: {$file->extension}\n";
echo "Mime-type: {$file->mime_type}\n";
echo "Does exist: {$file->exists}\n";
echo "Column 2 of the first row: ".($file->content[0]['col2'] ?? null)."\n";

// Output:
//  Size: 73B (73 bytes)
//  File name: my-file, File extension: csv
//  Mime-type: application/csv
//  Does exist: 1
//  Column 2 of the first row: cell2

// IMPORTANT:   Be careful directly using `$file->content`, every single time it would
//              physically read file as much times as you are reading that field.
//              So better always get content to `$content = $file->content;` variable


// What about writing to a file?

$data_to_save = [
    [
        'col1' => 'cell1',
        'col2' => 'cell2',
        'col3' => 'cell3',
        'col4' => 12.3,
    ],
    [
        'col1' => 'CELL 5',
        'col2' => 'CELL 6',
        'col3' => 'CELL 7',
        'col4' => 20,
    ],
];

// Writing CSV file
$file->content = $data_to_save;

// That would write the data to the file in CSV format :)

// Important to note: The FileProcessor is identified by the mime-type (and file
// extension)

// So just changing file extension from ".csv" to ".json"
// would cause data to be saved in a file in JSON string format:
$file = fl('my-file.json');

// Writing JSON file
$file->content = $data_to_save;

// Now "my-file.json" contains json-formatted string:
// [
//  {"col1":"cell1","col2":"cell2","col3":"cell3","col4":12.3},
//  {"col1":"CELL 5","col2":"CELL 6","col3":"CELL 7","col4":20}
// ]


// The coolest part - you can write your own "File Processor" and assign it
// to the mime-type! Then generation and parsing of a file would be the easiest thing ever

```

### Version objects and working with versions

`\spaf\simputils\models\Version` class allows to create version-objects and 
compare against each other.

```php
use spaf\simputils\models\Version;
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();

$my_version = new Version('1.2.3-rc', 'MyAPP');

// Keep in mind - this variable contains object, and still we can use "echo" with it
echo $my_version;
// Output: 1.2.3-RC

// Conversion of Version object to a string will be fully intuitive
// The same time, you can take a look on more detailed info like this:
print_r($my_version);
// Output:
//  spaf\simputils\models\Version Object
//  (
//    [software_name] => MyAPP
//    [parsed_version] => 1.2.3-RC
//  )

// Changing components of the version
$my_version->build_type = 'B';
$my_version->build_revision = 3;

$my_version->major = 99;

print_r($my_version);
// Output:
// spaf\simputils\models\Version Object
// (
//    [software_name] => MyAPP
//    [parsed_version] => 99.2.3-B3
// )

// Now let's make a check of required minimal version
$php_version = PHP::version();
// Current is "8.0.13" and minimum is "8.0.12" so check should work out
$minimum_version = new Version('8.0.12');

echo "Current PHP version: {$php_version}\n";
echo "Required PHP version: {$minimum_version}\n";

echo 'Is minimal required version satisfied: '
	.Str::from($php_version->gte($minimum_version))."\n";

// Output:
//  Current PHP version: 8.0.13
//  Required PHP version: 8.0.12
//  Is minimal required version satisfied: true

// Success! Our version is good enough. Let's check another case:

// Now, our patch version requirement is above our current PHP version
$minimum_version = new Version('8.0.14');

echo "Current PHP version: {$php_version}\n";
echo "Required PHP version: {$minimum_version}\n";

echo 'Is minimal required version satisfied: '
	.Str::from($php_version->gte($minimum_version))."\n";

// Output:
//  Current PHP version: 8.0.13
//  Required PHP version: 8.0.14
//  Is minimal required version satisfied: false

// Now, version is not satisfiable enough. The cool part that "RC" and "ALPHA" are as well
// being considered.

```

**Important:** If you have really uncommon version string format, the default parser will 
not be able to parse your version. It's recommended to be compatible with 
"Semantic Versioning", but in case if you want - you can implement your own version-parser.

### Advanced PHP Info object

```php
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();

// Getting default PHP Info object (it's being cached, so you can use `PHP::info()`
// without saving it to a variable!)
$php_info = PHP::info();

// Be careful output will be significant!
// And yes, if you use "echo" here it would be outputed as JSON string!
// No exception here :)
print_r($php_info);

```

Output would be:
```
spaf\simputils\models\PhpInfo Object
(
    [php_version] => spaf\simputils\models\Version Object
        (
            [software_name] => PHP
            [parsed_version] => 8.0.13
        )

    [simp_utils_version] => spaf\simputils\models\Version Object
        (
            [software_name] => SimpUtils
            [parsed_version] => 0.2.3
        )



    ... OUTPUT IS SO BIG THAT IT WAS STRIPPED ...



    [php_api_version] => spaf\simputils\models\Version Object
        (
            [software_name] => PHP API
            [parsed_version] => 20200930.0.0
        )

    [php_extension_version] => spaf\simputils\models\Version Object
        (
            [software_name] => PHP Extension
            [parsed_version] => 20200930.0.0
        )

    [zend_extension_version] => spaf\simputils\models\Version Object
        (
            [software_name] => Zend Extension
            [parsed_version] => 420200930.0.0
        )
)

```

The output left untrimmed exactly to show how much data is parsed and easily available.

Besides that all the version information is wrapped into `Version` objects

```php
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();

print_r(PHP::info()->zend_version);

// Output:
// spaf\simputils\models\Version Object
// (
//    [software_name] => Zend
//    [parsed_version] => 4.0.13
// )

// or with one line get "date.sunset_zenith" value of ini_config
echo PHP::info()->ini_config['date.sunset_zenith'];
// Output: 90.833333


```

### DotEnv and Env Vars

Out of the box, if you did not disable it (and called `PHP::init()` !) if `.env` 
file exists in your working directory (code directory) - it would load those variables
into `$_ENV` or `env()`

`.env` file content
```dotenv
PARAM_1="12.2"
PARAM_2="TEST test"
```


```php
use spaf\simputils\PHP;

// On the moment of `PHP::init()` call the file `.env` must exist
PHP::init();
// If this init is not called, or it's config disabling DotEnv init/bootstrap - then no
// variables will be loaded.

// `env()` without params returns content of `$_ENV` (so all the variables)
print_r(env());
// Output:
// spaf\simputils\models\Box Object
// (
//    ... all other vars are stripped out ...
//    
//    [PARAM_1] => 12.2
//    [PARAM_2] => TEST test
// )

// So "PARAM_1" and "PARAM_2" vars of our `.env` are loaded!


// Additionally you can easily generate `.env` file
// with mentioned above `File` infrastructure:
fl('.env')->content = [
	'my var 1' => 'Cool, variable name is auto-adjusted!',
	' SpEcIaL_____VaR ' => 'This variable name as well will be fixed',
];


```

The content of .env file now:
```dotenv
MY_VAR_1="Cool, variable name is auto-adjusted!"
SPECIAL_VAR="This variable name as well will be fixed"
```

As you can see names of the variables were adjusted. And `File` (`fl()`) infrastructure
allows to work with DotEnv files as well! Everything really transparently.

**Important:** Existing variables in the Linux/Container Environment are not 
being overwritten. So the system-wise env vars values are having precedence in front of
.env values. This allows absolute transparency for the "dev" and "prod" systems.


### Boxes or advanced arrays

`spaf\simputils\basic\box()` function is a shortcut for `new Box()`

```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\box;

PHP::init();

// It's just almost exactly as a native PHP array
$b = box(['my special value', 'another special value']);
print_r($b);
// Output:
// spaf\simputils\models\Box Object
// (
//    [0] => my special value
//    [1] => another special value
// )

// and if echoing/casting to string, it would produce json string
echo $b;
// Output:
//  ["my special value","another special value"]

// or like this displaying info about it inline of string generation:
echo "In my array of {$b} there are {$b->size} elements.\n";
echo "First slice of it would be {$b->slice(to: 1)}\n";
echo "And second slice of it would be {$b->slice(from: 1)}\n";
// Output:
//  In my array of ["my special value","another special value"] there are 2 elements.
//  First slice of it would be ["my special value"]
//  And second slice of it would be ["another special value"]

```

### Advanced Date and Time

```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\ts;

PHP::init();

// Getting current time ($dt will contain `DateTime` object)
$dt = now();
// Output:
// spaf\simputils\models\DateTime Object
// (
//    [date] => 2022-01-02 00:28:48.893927
//    [timezone_type] => 3
//    [timezone] => Europe/Berlin
// )

// Or stringifying it:
echo "Or current date-time-stamp is: {$dt}\n";
// Output: "Or current date-time-stamp is: 2022-01-02 00:30:33.390064"


// Working with custom time
$dt = ts('1990-02-22');

echo "My B-day is: {$dt->format(DT::FMT_DATE)}\n";
// Output: "My B-day is: 1990-02-22"

// Cool calculations, right?! :D
echo $dt->modify('+6 months -2 days +100 years')->format(DT::FMT_DATE);
// Output: 2090-08-20

```

**Important:** Currently not all planned is added to the `DateTime` object. But it's 
planned in the nearest time to enhance the functionality the same way as it was done for
`Box`s and `Version`s!


## Further documentation

[docs/use-cases.md](docs/use-cases.md)

-----

OLD

 2.   `spaf\simputils\SimpleObject` - simple lightweight object foundation allowing to use "setter/getter" methods functionality
      for properties similar to "Yii2" related functionality. (Recommended to do not use directly in the frameworks like Yii2.
      Because such frameworks already having nice functionality for getting/setting properties). 
      But for purpose of not only yii2 project compatibility, functionality can be used of course.
 6.   `/src/traits` - Contains traits for the initial functionality of the corresponding classes.
      The most of functionality is done through traits for the purpose of extensibility. 
      Those traits could be used in other frameworks/libs to inherit and improve the initial functionality of this one.

