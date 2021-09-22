# php-simputils
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


## Detailed description of functionality

### PleaseDie
This is simple dev/debugging method like "alert()" of JavaScript browser implementation. It prints out the data/vars
and then stops the execution.

The simple usage would be:

```php
<?php
use function spaf\simputils\basic\pd;

$my_var = 'Big and fat fox of a pink colour jumped over the internet';
$my_array = [
    'key1' => 'val1',
    'key2' => 99,
    'key3' => $my_var,
    4 => ['8', 15, [16, "23"], 42]
];
pd($my_array, $my_var);


```

output would be (or similar, because pd functionality is redefinable):
```
Array
(
    [key1] => val1
    [key2] => 99
    [key3] => Big and fat fox of a pink colour jumped over the internet
    [4] => Array
        (
            [0] => 8
            [1] => 15
            [2] => Array
                (
                    [0] => 16
                    [1] => 23
                )

            [3] => 42
        )

)

Big and fat fox of a pink colour jumped over the internet

```

#### Extension and Redefinability
Please Die functionality could be extended or even redefined. By another framework, or by another library, or by yourself. 

A good example is a small "proxy-lib" [spaf/yii2-simputils](https://github.com/PandaHugMonster/yii2-simputils) that
redefines the pd() underlying functionality with a better outputting mechanism like "VarDumper". 

Even though through this "proxy-lib" you redefine functionality, you don't need to change an import and a call of "pd()"
anywhere in your code, because the call stays the same, but the different functionality is being used.
