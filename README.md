# SimpUtils

Minimum required PHP version is **8.0**

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

$config = PHP::init();

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

echo "{$file->size_hr} ({$file->size} bytes)\n";
echo "File name: {$file->name}, File extension: {$file->extension}\n";
echo "Mime-type: {$file->mime_type}\n";
echo "Does exist: {$file->exists}\n";
echo $file->content[0]['col2'] ?? null;
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

-----

OLD

**Warning:** Minimum required version of PHP is **8.0** (Was reconsidered from 8.1 back to 8.0) 

Micro-framework extending PHP language with some useful perks, partly can even remind python 3 development capabilities.

This lib (and related other libs) I develop the mostly for myself, but you are absolutely welcome to use it/those for your own good.

At this context the words "lib" and "framework" both refers to the same meaning of "micro-framework".

**Important:** The code is unfinished in a matter of structure. If you are interested in the lib and it's functionality - 
please wait until stable release of **1.0.0** (currently it's **0.2.3**). Starting from that semantic version,
existing architecture most likely will not change for existing components (at least until the next major version change). 
More about semantic versioning: [Semantic Versioning Explanation](https://semver.org). 

## Installation

Install it through composer:
```shell
composer require spaf/php-simputils "*"
```


## Recent changelog (0.2.3)
 * Added minimal Logger facility
 * Improved PSR-4 files structure (namespace prefix instead of full directories root structure)
 * DotEnv (TODO)
 * Files and Object Conf Files (TODO)
 * Overall improvements (fine-tuning of architecture)
 * Error and Exception flexible handling (TODO)


## Framework short description
The framework currently has:
 1.   `spaf\simputils\helpers\DateTimeHelper` - static class with DateTime related functionality, enables to use **int|DateTime|string** formats
      transparently. Underlying normalization will understand any of those types and convert them to a proper DateTime object.
 2.   `spaf\simputils\SimpleObject` - simple lightweight object foundation allowing to use "setter/getter" methods functionality
      for properties similar to "Yii2" related functionality. (Recommended to do not use directly in the frameworks like Yii2.
      Because such frameworks already having nice functionality for getting/setting properties). 
      But for purpose of not only yii2 project compatibility, functionality can be used of course.
 3.   `spaf\simputils\System` - The helping class related to the platform and system information (will be improved further)
 4.   `spaf\simputils\Settings` - Simputils storage for runtime/non-runtime settings of the application and framework.
 5.   `/src/basic.php` - file containing procedures and useful shortcuts. At this point only [pd()](#PleaseDie) functionality
 6.   `/src/traits` - Contains traits for the initial functionality of the corresponding classes.
      The most of functionality is done through traits for the purpose of extensibility. 
      Those traits could be used in other frameworks/libs to inherit and improve the initial functionality of this one.

## Use-Cases
__Examples are demonstrated in use-cases__


Here you can find all the considered use-cases (and examples for them): [docs/use-cases.md](docs/use-cases.md)
