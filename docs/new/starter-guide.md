# Starter Guide

The following few parts of this guide should give you a brief look into the capabilities 
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
class MyClass2 extends SimpleObject {

	#[Property('explicit_type_of_property', type: 'get')]
	protected function testTEST(?string $test) {
		pr("### Property \"explicit_type_of_property\" getting has just happened.");
		pr("### Incoming value is: \"{$test}\".");
		return "Bebebe!";
	}

}

$obj = new MyClass2;

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

There is a way to have explicit control over the Virtual Property 
without "getter/setter" methods. You need to define special protected or 
private **field**, then assign `Property` attribute to it like that:

```php
use spaf\simputils\attributes\Property;


/**
 * @property $field_1
 * @property $renamed_prop
 * @property-read $get_only_prop
 */
class MyClass3 extends SimpleObject {

    #[Property]
	protected $_field_1 = null;

    #[Property('renamed_prop')]
	protected $_field_2 = null;

    #[Property(type: 'get')]
	protected $_get_only_prop = null;

}

```

Notice that initial `_` (underscore) is removed. It's highly important to avoid shadowing
the existing Properties with Virtual ones. The Class internally must have access to all
Properties (virtual and normal ones) at all times!

So at this point, you will have access of specified type to the "protected field" through
Virtual Properties.

You can even combine approaches of the one above + getter/setter method:

```php
use spaf\simputils\attributes\Property;


/**
 * @property ?string $my_internal_ro_value
 */
class MyClass4 extends SimpleObject {

	#[Property(type: 'get')]
	protected ?string $_my_internal_ro_value = "WutWutWut!?!?";

	#[Property('my_internal_ro_value')]
	protected function setMyInternalRoValue(?string $val) {
		// Pre-processing before saving the value!
		// IMP  Make sure you are saving to the final variable, otherwise you
		//      will get a very nasty loop!
		$this->_my_internal_ro_value = "(( {$val} ))";
	}

	function doSomething() {
		$this->_my_internal_ro_value = "Saving something internally without brackets!";
	}

}

$obj = new MyClass4;

pr("Initial value reading: {$obj->my_internal_ro_value}");
$obj->my_internal_ro_value = "Value is set from outside";

pr("New outside assigned value reading: {$obj->my_internal_ro_value}");
$obj->doSomething();

pr("New inside assigned value reading: {$obj->my_internal_ro_value}");

```

Output:
```text
Initial value reading: WutWutWut!?!?
New outside assigned value reading: (( Value is set from outside ))
New inside assigned value reading: Saving something internally without brackets!
```

##### Validators and Normalizers
Tightly coupled (but not yet fully refactored and improved) is the concept of Validators.

Validator - is the method/function/class that applied before assigning value to 
a Virtual Property (Yes, validation and normalization works out of the box only with 
Virtual Properties). And either let it pass, or replaces it with `null`.
Normalizer - is the method/function/class that applied before assigning value to 
a Virtual Property and either let it pass as-is, or modifies it to something else 
or another form of the entity (example: Turning a string literal to an object of 
the specific class).

**Important**: Those concepts are finished, but architecture and some aspects of usage 
is to be improved in the future (In backward compatible way of course).

Validators and Normalizers are usually automatically applied based on 
the specified data-type (only a single type +- `null` is supported at the moment, 
if multiple data-types are specified, automatic validation and/or normalization 
is disabled, and you would need to do the explicit one).

P.S. A lot of documentation about that will be omitted for now, until the functionality is
refactored. If you need more details, please take a look into the code.

Example:
```php

/**
 * @property ?DateTime $my_dt
 */
class MyClass10 extends SimpleObject {

	#[Property]
	protected ?DateTime $_my_dt = null;

}

$obj = new MyClass10;

$obj->my_dt = "2022-12-25 05:00:00";

pr("Normalized value: ", $obj->my_dt);

```

Output:
```text
Normalized value: 
spaf\simputils\models\DateTime Object
(
    [_simp_utils_property_batch_storage] => Array
        (
        )

    [_orig_value:protected] => 
    [date] => 2022-12-25 00:00:00.000000
    [timezone_type] => 3
    [timezone] => America/Toronto
)
```

As you can seem the value that is stored in that variable is a DateTime object, and 
not the string we assigned.

You can write your own validators, but refer to the code-base for the info about that.

More documentation will be available after the refactoring is done on this functionality.

The above example was with a default data-type normalization, but you can 
explicitly specify your validator/normalizer when assigning a Virtual Property.

```php

use spaf\simputils\components\normalizers\IntegerNormalizer;


/**
 * @property ?float $my_float
 */


class MyClass20 extends SimpleObject {

	#[Property(valid: IntegerNormalizer::class)]
	protected ?float $_my_float = null;

}

$obj = new MyClass20;

$obj->my_float = 200.404;

pr("Normalized value: ", $obj->my_float);

```

Output:
```text
Normalized value: 
200
```

Look, despite specifying the data-type as `float` we still getting the integer with 
a trimmed out floating-point part. It's because we are applying a custom Normalizer of
`\spaf\simputils\components\normalizers\IntegerNormalizer`, so it turns value to `integer`
on the fly and stores it in float. Float type can be considered as an extension of integer,
so it can store integer as well.


### Debugging and Observing
There are a multiple ways to debug your variables and their content like `pd()` "please die",
and related methods `pr()`, `prstr()`

 * `pd()` - "Please Die" outputs all the supplied arguments in the debugging mode, and 
   then terminate the application (if not redefined by user)
 * `pr()` - "Print" outputs all the supplied arguments like `pd()` in the debugging mode,
   nut does not terminate the application
 * `prstr()` "Print as string" the same behaviour as `pr()` but instead of outputing
   it to stdout, it returns the string of the result

All the above functions display the content of the variables in the debugging mode,
what means to display some internal properties and show objects with their 
internal content. If you want to display the variable in the stringified way
please wrap/turn the variable into the string before providing it. 
All the strings are displayed as-is.

Example:
```php
/**
 */
class MyClass100 extends SimpleObject {

	public $normal_public_property = "I'm normal public";
	protected $normal_protected_property = "I'm normal protected";
	private $normal_private_property = "I'm normal private";

	#[Property]
	protected function getReadOnlyVirtualProperty(): string {
		return "I'm a Read-Only Virtual Property";
	}

	#[Property('read_only_named_virtual_property')]
	protected function getRONVP(): string {
		return "I'm a Read-Only Named Virtual Property";
	}

	#[Property]
	protected ?string $_another_virtual_property = "I'm another virtual property";

	#[Property(type: Property::TYPE_SET)]
	protected ?string $_write_only_virtual_property = "I will not get displayed";

}

$obj = new MyClass100;

pr("Displaying the object in the debugging mode: ", $obj);

```

Output:
```text
Displaying the object in the debugging mode: 
MyClass100 Object
(
    [another_virtual_property] => I'm another virtual property
    [getReadOnlyVirtualProperty] => I'm a Read-Only Virtual Property
    [normal_private_property] => I'm normal private
    [normal_protected_property] => I'm normal protected
    [normal_public_property] => I'm normal public
    [read_only_named_virtual_property] => I'm a Read-Only Named Virtual Property
)
```

As you can see above all the displayable properties (virtual or real) are displayed.
They were sorted though, keep that in mind, but they are all displayed in this way.

Interesting part is that even the private and protected properties are displayed 
(except static ones!). The Virtual Properties are also displayed in the same way, 
but only those that implement "get" type (as minimum read-only ones). If you have 
a property that write-only - it will not be displayed.

To hide or "obfuscate" the displayed properties in this way you can use
a PHP Attribute `#[DebugHide]` before your property (doesn't matter if virtual or 
the real one). By default it will hide the output of this property at all.
If you want to hide the real value (password or secret) you can provide the first
argument of this attribute as `false`, and (optional) if you would provide the second 
argument it will use it as a "hiding" template.
**Important**: This would hide the values only for the debugging functions,
it will not affect the real value which will be still available if you will access
the property from the code.
**Important**: There is a few bugs of the PHP engine that might not fully work well
on some native PHP classes like `DateTime` and can cause inconsistent output in those 
cases, but it's only applicable to the standard native PHP classes and inherited from those,
does not affect your custom classes.

Example:
```php

/**
 */
class MyClass200 extends SimpleObject {

	public $normal_public_property = "I'm normal public";

	#[DebugHide]
	protected $normal_protected_property = "I'm normal protected";

	#[DebugHide(false, '!!!! -- !!!!')]
	private $normal_private_property = "I'm normal private";

	#[Property]
	protected function getReadOnlyVirtualProperty(): string {
		return "I'm a Read-Only Virtual Property";
	}

	#[Property('read_only_named_virtual_property')]
	protected function getRONVP(): string {
		return "I'm a Read-Only Named Virtual Property";
	}

	#[DebugHide(false)]
	#[Property]
	protected ?string $_another_virtual_property = "I'm another virtual property";

	#[Property(type: Property::TYPE_SET)]
	protected ?string $_write_only_virtual_property = "I will not get displayed";

}

$obj = new MyClass200;

pr("Displaying the object in the debugging mode: ", $obj);
pr("==================");
pr("Displaying the object as a string (JSON is the default): \n{$obj}");
pr("==================");
pr("Displaying the object as an explicit JSON (with pretty output): \n{$obj->toJson(true)}");


```

Output:
```text
Displaying the object in the debugging mode: 
MyClass200 Object
(
    [another_virtual_property] => ****
    [getReadOnlyVirtualProperty] => I'm a Read-Only Virtual Property
    [normal_private_property] => !!!! -- !!!!
    [normal_public_property] => I'm normal public
    [read_only_named_virtual_property] => I'm a Read-Only Named Virtual Property
)

==================
Displaying the object as a string (JSON is the default): 
{"another_virtual_property":"I'm another virtual property","getReadOnlyVirtualProperty":"I'm a Read-Only Virtual Property","normal_private_property":"I'm normal private","normal_protected_property":"I'm normal protected","normal_public_property":"I'm normal public","read_only_named_virtual_property":"I'm a Read-Only Named Virtual Property"}
==================
Displaying the object as an explicit JSON (with pretty output): 
{
    "another_virtual_property": "I'm another virtual property",
    "getReadOnlyVirtualProperty": "I'm a Read-Only Virtual Property",
    "normal_private_property": "I'm normal private",
    "normal_protected_property": "I'm normal protected",
    "normal_public_property": "I'm normal public",
    "read_only_named_virtual_property": "I'm a Read-Only Named Virtual Property"
}
```

**Important**: As was stated earlier, some objects due to some bugs might be displayed with
some unnecessary fields/rubbish

**Important**: Keep in mind that logic of "debugging" is different than logic of converting
the object to JSON or other formats.

### JSON and data representation

JSON is used by default as a string representation of the objects related to the framework,
unless their "toString" magical method is not redefined for fine-tuned case.

JSON String representation is not (or not always) suitable for the debugging,
especially if it contains sensitive data.

JSON String format is used to easily turn object to it's strinigified JSON form,
what is useful for APIs and data storing.

Despite it works in a similar way as debugging functionality, it actually
differs. Compare the output of those 2 versions:
```php
/**
 */
class MyClass300 extends SimpleObject {

	public $normal_public_property = "I'm normal public";

	#[DebugHide]
	protected $normal_protected_property = "I'm normal protected";

	#[Extract(comment: 'Proceed with extraction')]
	#[DebugHide(false, '!!!! -- !!!!')]
	private $normal_private_property = "I'm normal private";

	#[Property]
	protected function getReadOnlyVirtualProperty(): string {
		return "I'm a Read-Only Virtual Property";
	}

	#[Extract(false, comment: 'Avoiding the extraction of this property')]
	#[Property('read_only_named_virtual_property')]
	protected function getRONVP(): string {
		return "I'm a Read-Only Named Virtual Property";
	}

	#[Extract(false, comment: 'Avoiding the extraction of this property')]
	#[DebugHide(false)]
	#[Property]
	protected ?string $_another_virtual_property = "I'm another virtual property";

	#[Property(type: Property::TYPE_SET)]
	protected ?string $_write_only_virtual_property = "I will not get displayed";

}

$obj = new MyClass300;


pr($obj->toJson(true));
pr("============================");
pr($obj);
```

Output:
```text
{
    "getReadOnlyVirtualProperty": "I'm a Read-Only Virtual Property",
    "normal_private_property": "I'm normal private",
    "normal_protected_property": "I'm normal protected",
    "normal_public_property": "I'm normal public"
}
============================
MyClass300 Object
(
    [another_virtual_property] => ****
    [getReadOnlyVirtualProperty] => I'm a Read-Only Virtual Property
    [normal_private_property] => !!!! -- !!!!
    [normal_public_property] => I'm normal public
    [read_only_named_virtual_property] => I'm a Read-Only Named Virtual Property
)
```

As you can see, the JSON output is different to debugging output.
This is important for logging, especially if your object contains sensitive data
like password or secret, it's always better to do not store clear JSON representation,
but rather to use `prstr()` or `pr()` debugging functionality, so you cover those
properties with sensitive data.


### ExecEnvs aka Execution Environments aka Stages
Something commonly called as "stages" like "prod", "dev", "demo", etc. in this framework
bears the name "ExecEnv" or "Execution Environment". Naming is not ideal, but allows
to avoid collisions and misunderstandings.

Take a look at this code, it might look a bit overwhelming, but in fact it's really simple.

```php

$ic = PHP::init([
	'ee' => 'prod',
]);

function exp1() {
	$ic = ic();
	if ($ic->ee->is('dev')) {
		pr("## I'm DEV stage");
	}

	if ($ic->ee->is('prod')) {
		pr("## Wow PROD stage");
	}

	if ($ic->ee->is('demo')) {
		pr("## Hm DEMO stage");
	}

	if ($ic->ee->is_local) {
		pr("#### Attention! It's very LOCAL!");
	}
}


pr($ic->ee);

exp1();

// Ignore this line for now
pr(Str::mul('=', 20));


$ic->ee = 'demo';

exp1();


// Ignore this line for now
pr(Str::mul('=', 20));


$ic->ee = 'dev-local';

exp1();

```

Output:
```text
spaf\simputils\generic\BasicExecEnvHandler Object
(
    [is_hierarchical] => 
    [is_local] => 
    [permitted_values] => spaf\simputils\models\Box Object
        (
            [1] => prod
            [2] => demo
            [3] => dev
        )

    [value] => prod
)

## Wow PROD stage
====================
## Hm DEMO stage
====================
## I'm DEV stage
#### Attention! It's very LOCAL!
```

**Important**: It's strongly suggested to do not modify stage on the fly. It's considered 
a bad practice. Can cause severe problems for you, but for purpose of the example it's 
suitable.

As you can see we have an variable `ee` with an object of 
`spaf\simputils\generic\BasicExecEnvHandler`. It's our ExecEnv handler.

In `PHP::init()` settings you can specify string name only if you wish, but
the framework will turn it to an object with the correct value.

You can see in the output that permitted values are only the one that listed. 
In case of need this can be replaced with your custom list of namings.

The function `exp1()` is checking step by step if the exec-env value is fitting,
and if not - skipping the code of the condition.

Additionally it checks at the end of the function whether the exec-env is local or not.

What is `local` in this context - it's an additional granularity of the stage recognition.

For example if you have "dev", "demo" and "prod" deployed versions, but when developing
the local code (on your machine, or on your docker instance of your local machine) 
you want to have additional stuff but available for the local development only.

**Important**: `local` should never be used on any deployed version of code, it's
considered a very bad practice!

`local` is usually specified through "-" (dash) of the stage name directly in the string,
where you specify the stage name (This is super handy with combination of DotEnv and 
Environmental variables!)

**Important**: More deep functionality is not discussed at this point, but it's
important to note that `local` exec-env will return true for all non `local` of the same
stage. For example `dev` stage will not return true for `dev-local` conditions,
but `dev-local` stage will return true for both `dev` and `dev-local` conditions!


## Part 2 - Advanced


## Part 3 - Some Use-Cases


## Part 4 - Best Practices
