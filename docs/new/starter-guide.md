# Starter Guide

The following few parts if this guide should give you a brief look into the capabilities 
of the framework.

## Part 1 - Basics

### Intro

Some parts of the framework are ready to be used right out of the box without any initialization.
But some parts require some initialization, for that reason it's recommended always start your app
from framework initialization like this (ideally it should be "the first thing to run" 
after composer autoload):
```php

use spaf\simputils\PHP;

// Composer autoload
require_once 'vendor/autoload.php';

// SimpUtils framework init
PHP::init();

```

**Important**: Keep in mind, in the examples further this initialization code could be omitted 
for convenience of reading, but you always have to use it when coding. 

The `PHP::init()` is the initialization method of the framework. 
It returns "IC" (init-config), so you could use it right away, but if you need it later, 
you always can pick it up through `ic()` function (IC can be obtained only after 
initialization!).

```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\ic;

$ic = PHP::init();

// OR

$ic = ic();

pr("{$ic}");

```

Output:
```text
InitConfig[name=app, code_root=/home/ivan/development/php-simputils, working_dir=/home/ivan/development/php-simputils, init_blocks=["spaf\\simputils\\components\\initblocks\\DotEnvInitBlock"]]
```

Beside that, you could provide some parameters for the initialization.
For examples you could redefine some components, change/set localization and timezone,
change some aspects like exec-environments and/or activate or deactivate additional 
initialization blocks (.env autoimport, etc.)

```php

$ic = PHP::init([
	'name' => 'test', // For the main app it's suggested against custom name (default is "app")

	'code_root' => '/tmp',
	'working_dir' => '/tmp/wd',

	'init_blocks' => [], // Deactivating all the default init-blocks
	'disable_init_for' => [DotEnvInitBlock::class], // Has a purpose only if not deactivated all "init_blocks"
	'allowed_data_dirs' => ['data', 'data2', 'config', 'settings'],
	'big_number_extension' => BigNumber::SUBSYSTEM_BCMATH,
	'default_host' => 'tetris',
	'l10n' => 'GB',
	'default_tz' => 'America/Toronto',
	'ee' => 'demo-local',
	'redefinitions' => [
		InitConfig::REDEF_PD => fn($x) => (print " == {$x} == "),
	],
]);

```

Example above includes the basic settings, you always can create your own InitConfig class, 
and provide an object of it as an argument of the `PHP::init()`.

Short explanation of the basic settings:
1. `name` - by default is "app". Should not be customized for the main app, necessary for
   modules and libraries. Basically defines the key-name for the init config (
   SimpUtils can store InitConfig for each separate library/module, the name must be unique,
   and the exact init-config can be retrieved by this unique key-name)
2. `code_root` - specifying the code-base root path. By default identified by 
   the entry-script, but can be adjusted in case the entry script is locating not in 
   the code-base root.
3. `working_dir` - If not specified the default value is taken from `code_root` value.
   This folder is considered the working directory with configs and non-code files 
   relevant to the app like json/php configs, etc. (Keep in mind that `FS::data()` 
   and relevant functionality, uses this as the starting point)
4. `init_blocks` - Bootstrapping code-blocks (classes) that should be initialized after
   the framework is initialized. By default includes some minimal set 
   of blocks (DotEnv autoimport, etc.).
5. `disable_init_for` - complimentary array of the InitBlocks to disable their bootstrapping. 
   For example to disable "DotEnv autoimport", etc.
6. `allowed_data_dirs` - directories that are allowed to get configs through `FS::data()` 
   functionality. Calculated from the `working_dir` base-path.
7. `big_number_extension` - Type of extension to use for `BigNumber` calculations
   (`gmp` or `bcmath`).
8. `default_host` - to redefine "localhost" expected host name with something else
9. `l10n` - Specifying the localization (2 letters of the country). 
   List can be found here "php-simputils/src/data/l10n"
10. `default_tz` - the default timezone for the DateTime infrastructure
11. `ee` - Setting Execution Environment value (deployment stages: `demo`, `prod`, `dev`, etc.)
12. `redefinitions` - Specify custom classes/callables for some major components of 
    the framework (Models, some functions, etc.) 

### Framework structure and organization

This sub-section explains the internal organization, it's a theoretical part,
it is good to know, but does not provide any practical examples of the framework usage.
It's still recommended to read and know those aspects!

The framework is "purely" based on OOP. Except some special cases like "shortcuts".

The framework divided to some abstract groups of code files:
1. "**basic**" - this is the only place where functions are not being part
   of the OOP classes, but only because considered "shortcuts" which
   are preferred way of using them instead of the direct class usages.
   They usually must not contain logic inside, but use underlying functionality of the "Helpers".
   (Internals of the core framework code-base is not allowed to use them.
   All the internal invokes must be done with non-shortcut calls of
   the direct class members! **Exception is only for Test Suite and outer usages!**).
2. "**Helpers**" - The group of static classes (the mostly in the root folder of source code-base)
   that provide ultimate functionality to their respective topics (Example: `\spaf\simputils\PHP`
   `\spaf\simputils\FS`, `\spaf\simputils\Math`, etc).
3. "**Models**" - The group of normal classes that use 
   `\spaf\simputils\traits\SimpleObjectTrait` trait, or extended 
   from `\spaf\simputils\generic\SimpleObject` base class. Are used for data representation!
   Must be used as objects, should not be abstract or static!
4. "**Generics**" or "**Base Classes**" - The set of base classes that the mostly should be
   abstract and should be used only to let others to base their classes on them.

There could be some more groups of code-base entities, but they could be highly specific 
and less universal.

The most major part of the framework is `\spaf\simputils\PHP` helper. It provides a lot of
useful functionality, and used for the very first initialization of your application.

It contains some settings and some methods to improve or alter behaviour of your app.

#### Key feature of the framework
This framework has lots of capabilities under the hood, but there is a set of the highlights.

##### Virtual Properties (advanced getters and setters)

You can create getters and setters really easily from methods of the class, or even from 
the class fields/variables to control, validate and pre-process incoming values.

Keep in mind, that example will show just a few options of many how to organize your
Virtual Properties. Virtual Properties work in a similar way as the one you specify directly 
in the class as "Class Variables".

To use this functionality you must either inherit your class 
from `spaf\simputils\generic\SimpleObject` or 
to use trait `\spaf\simputils\traits\SimpleObjectTrait` (This is useful for overcoming 
the "multiple inheritance" problem of PHP, just apply this trait on the base classes of yours)

**Important**: This functionality works only on objects, the static versions of those 
is not possible to implement without modifying the code-base of the PHP engine itself :( ...

Example 1

```PHP

use spaf\simputils\attributes\Property;use spaf\simputils\generic\SimpleObject;

/**
 * @property-read ?string $ro_prop1
 * @property-write ?string $rw_prop2
 * @property ?string $prop3
 */
class MyClass1 extends SimpleObject {

	#[Property('ro_prop1')]
	protected function getRoProp1BeBeBebbbbbbaaaaa(): ?string {
		pr("### Property \"ro_prop1\" getting has just happened.");
		return "I am always being returned!";
	}

	#[Property('rw_prop2')]
	protected function setProp2(?string $val) {
		$void_var = $val;
		pr("### Property \"rw_prop2\" setting has just happened: {$void_var}");
	}

	#[Property('prop3')]
	protected function bothProp3(?string $val, $type): ?string {
		if ($type === Property::TYPE_GET) {
			pr("### Property \"prop3\" getting has just happened.");
			return "Something Something";
		} else {
			$void_var = $val;
			pr("### Property \"prop3\" setting has just happened: {$void_var}");
        }

		return null;
	}

}

$obj = new MyClass1;

pr($obj->ro_prop1);
pr($obj->prop3);

$obj->prop3 = 'TEST1';
$obj->rw_prop2 = 'TEST2';

```

Output:
```text
### Property "ro_prop1" getting has just happened.
I am always being returned!
### Property "prop3" getting has just happened.
Something Something
### Property "prop3" setting has just happened: TEST1
### Property "rw_prop2" setting has just happened: TEST2
```

The example 1 demonstrates the way to use getters/setters through a single method 
(here "singleton-prop").

The type of "getter", "setter" or "both" is identified by the method signature's 
**returned type** (getter) and **first parameter type** (setter).
In case if you need an explicit way to set the type, you can add the named param `type`
to the Property attribute description:

```php

/**
 * @property-read ?string $explicit_type_of_property
 */
class MyClass1 extends SimpleObject {

	#[Property('explicit_type_of_property', type: 'get')]
	protected function testTEST(?string $test) {
		pr("### Property \"explicit_type_of_property\" getting has just happened.");
		pr("### Incoming value is: \"{$test}\".");
		return "Bebebe!";
	}

}

$obj = new MyClass1;

pr($obj->explicit_type_of_property);

```

Output:
```text
### Property "explicit_type_of_property" getting has just happened.
### Incoming value is: "".
Bebebe!
```

As you can see the explicit property type specification changed the behaviour, 
despite the method signature.




### Debugging and Observing
There are a multiple ways to debug your variables and their content like `pd()` "please die",
and related methods `pr()`, `prstr()`


## Part 2 - Advanced


## Part 3 - Some Use-Cases


## Part 4 - Best Practices
