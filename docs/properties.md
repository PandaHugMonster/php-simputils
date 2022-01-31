[<< Back to README.md](../README.md)

----

# Properties - Getters and Setters

Small term definition, any of those terms are interchangeably used (within a group): 
 * read-only / get / getter
 * write-only / set / setter
 * read-write / both / getter-setter

Except inside of the code, string literals would be: `get`, `set`, `both`


## Index

 1. [Intro](#Intro)
 2. [Property attribute](#Property-attribute)
    1. [Basics of Property](#Basics-of-Property)
    2. [Property attribute arguments](#Property-attribute-arguments)
    3. [Signature Definition Rules](#Signature-Definition-Rules)
       1. [Property-Getter](#Property-Getter)
       2. [Property-Setter](#Property-Setter)
       3. [Property-Both (Getter + Setter)](#Property-Both-Getter--Setter)
       4. [Property Getter-Setter reference table](#Property-Getter-Setter-reference-table)
 3. [PropertyBatch attribute](#PropertyBatch-attribute)
    1. [Basics of PropertyBatch](#Basics-of-PropertyBatch)
 4. [Debugging and printing out](#Debugging-and-printing-out)
    1. [DebugHide attribute](#DebugHide-attribute)
       1. [Hiding the whole field](#Hiding-the-whole-field)
       2. [Usage of DebugHide](#Usage-of-DebugHide)


## Intro

Properties are "object-variables" that can store data related to a particular object.
Usually "property" term is an alias of "field", but in the framework there is 
a following terms definition:
 * Under **properties**, **virtual properties** or **Dynamically defined properties** 
   meant "object-variables" with help of `\spaf\simputils\attributes\Property` or 
   `\spaf\simputils\attributes\PropertyBatch`. (Basically getter-setter ones)
 * **Statically defined properties** can be used to refer to "object-variables" that
   are opposite to **virtual properties** (**Dynamically defined properties**)
 * Under **fields** meant all the "object-variables" that can be accessed
   through direct name (Like: `$my_object->my_field`).


If you are not familiar with the concept of PHP Attribute, you can read here: 
[PHP Attributes](https://php.watch/articles/php-attributes)

Properties functionality is done through `\spaf\simputils\attributes\Property` or 
`\spaf\simputils\attributes\PropertyBatch` attributes + `__get` / `__set` magic methods.

To use that functionality you need to add that capabilities to your classes. In the best case
you would create a single "Base" class or something like that, and then add it to that class.

There are basically 3 ways of doing that:
 1. The most preferable and suggested is to extend your class 
    from `\spaf\simputils\generic\SimpleObject`.
 2. In case if you have a very base class already inherited from another framework base class,
    you can apply the same functionality by using `\spaf\simputils\traits\SimpleObjectTrait` trait
    in your base class
 3. Strongly recommended against it. Exclusively support only of properties attributes 
    could be done by using `\spaf\simputils\traits\PropertiesTrait` in your base class(es). 

and then just apply `Property` or `PropertyBatch` attribute to content of your inherited classes.

Here is a simple example (you are not limited to use 1 additional class in between, 
but it's really recommended):

```php

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use function spaf\simputils\basic\pr;


class BaseClassA extends SimpleObject {

}

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 */
class MyTargetClassB extends BaseClassA {

	public $my_sd_field = 'Ho Ho Ho';

	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}
}

$obj = new MyTargetClassB();

pr("{$obj->my_sd_field}! Happy New Year and wonderful year {$obj->year}");
pr($obj);

```

Output would be similar to:
```php
Ho Ho Ho! Happy New Year and wonderful year 2022
MyTargetClassB Object
(
    [year] => 2022
    [obj_id] => 117
    [obj_type] => MyTargetClassB
    [my_sd_field] => Ho Ho Ho
    [reindeer] => Array
        (
            [0] => Dasher
            [1] => Dancer
            [2] => Prancer
            [3] => Vixen
            [4] => Comet
            [5] => Cupid
            [6] => Donner
            [7] => Blitzen
            [8] => Rudolph
        )

    [cat] => Tom
)

```


## Property attribute

### Basics of Property

`\spaf\simputils\attributes\Property` - is the main attribute for this functionality.
It allows to mark class methods as a "property", so it could be access later. 
And because it is a method, it will be executed everytime when "property" is accessed.

For fields, there are basically 2 ways to access them:
 * Reading (get/getter)
 * Writing (set/setter)

Though there are mentioned 2 ways above, for a method there are third type called "both".
This third way specifies method to be called in both cases (the same method!)

Example:

```php

use spaf\simputils\attributes\Property;

/**
 * @property-read $get_method_only
 * @property-write $set_method_only
 * 
 * @property $both_methods_at_once
 */
class MyTargetClassC extends BaseClassA {
    
    #[Property('get_method_only', Property::TYPE_GET)]
    protected function getMethodOnly() {/*...*/}
    
    #[Property('set_method_only', Property::TYPE_SET)]
    protected function setMethodOnly() {/*...*/}
    
    #[Property('both_methods_at_once', Property::TYPE_BOTH)]
    protected function bothMethodsAtOnce() {/*...*/}
    
}

```

As you can see, there is defined 1 read-only field, 1 write-only field, and
1 read-write field.

**Important:** Even though you can have 1 method for both read-write functionality. 
It's a good practice to have 1 method for "read" and 1 method for "write" separately.

Example:

```php

/**
 * @property $my_value
 * @property-read $my_second
 */
use spaf\simputils\attributes\Property;
class MyTargetClassD extends BaseClassA {
    
    #[Property('my_value', Property::TYPE_GET)]
    protected function getOne() {/*...*/}
    
    #[Property('my_value', Property::TYPE_SET)]
    protected function setOne() {/*...*/}
    
    #[Property('my_second', Property::TYPE_GET)]
    protected function getAnother() {/*...*/}
    
}

```

As demonstrated above you can use 2 independent methods for that.
And in case if you need to change mode - just comment out one of the Attributes 
on those methods.

----

### Property attribute arguments

All the examples above use 2 arguments each time for Property definition.
And I need to mention that both of them are technically optional.

First argument or `$name` represents the Property name, by which you can access the Property.
If you would omit it like this:

```php

/**
 * @property $getOne
 */
use spaf\simputils\attributes\Property;

class MyTargetClassE extends BaseClassA {
    
    #[Property(type: Property::TYPE_GET)]
    protected function getOne() {/*...*/}
    
}

```

In this case you property name would be the same as method name "getOne".

**Important:** This is almost always a bad idea, due to rare but possible name collision +
it's highly counterintuitive + that blocks your flexibility in choosing "camelCase" or "snake_case"
different format for methods and for properties. Besides it's just really messing up 
and slowing down the code maintenance and analysis.

**Good practice:** Always provide first argument with the expected property name.


Ok, first argument is a name of the property, but then the second argument or `$type`
is really optional if you follow the "[Signature Definition Rules](#Signature-Definition-Rules)"...

### Signature Definition Rules

If second or `$type` argument is omitted, then the type of the property method is identified 
by the method signature

**Good practice:** In the most cases it's the cleanest and the most comfortable to work with way

So let's start from the very simple one, the most minimalistic one:
```php

/**
 * @property-read $method_name
 */
use spaf\simputils\attributes\Property;

class MyTargetClassF extends BaseClassA {
    
    #[Property('method_name')]
    protected function methodName() {/*...*/}
    
}

```

Worth to mention - modifiers like `protected`, `public`, `private` are not affecting 
the Property itself, but for sure can affect the method execution (and even can cause exceptions!).

For now let's always use `protected`.

then we have `function` which is kinda "unchangeable" :).

Method name is irrelevant for method type definition.

But then goes params definition and through `:` symbol goes return-type definition (which
is omitted here).

Those 2 play the main role in method type definition.

**Important:** Explicitly defined value for parameter `$type` of the Property attribute -
enforces the type, and the signature of a method is ignored!

----

So rules are:

#### Property-Getter
 1. If there are **no arguments** + **no return-type definition** - it is `GETTER`!
 2. If there are **no arguments** + **any return-type except `void` or `never`** - it is `GETTER`!

Example:
```php

/**
 * @property-read $method_name
 */
 
use spaf\simputils\attributes\Property;

/**
 * @property-read $method_name_1
 * @property-read $method_name_2
 */
class MyTargetClassG extends BaseClassA {
    
    // Getter rule 1: no arguments + no return-type definition
    #[Property('method_name_1')]
    protected function methodName1() {/*...*/}
    
    // Getter rule 2: no arguments + any return-type except "void"/"never"
    #[Property('method_name_2')]
    protected function methodName2(): string|int|null {/*...*/}
    
}

```

**Important:** `null` return-type indicating "getter", despite the meaning similar to `void`.

**Important:** The "return" directive of the method body code - does not play role 
in the indication of the method type. **Only "method signature" matters for that!**

#### Property-Setter
 1. If there is **at least 1 argument** + **no return-type definition** - it is `SETTER`!
 2. If there are **return-type `void` or `never`** - it is `SETTER`!

Example:
```php

/**
 * @property-read $method_name
 */
 
use spaf\simputils\attributes\Property;

/**
 * @property-write $method_name_1
 * @property-write $method_name_2
 * @property-write $method_name_3
 */
class MyTargetClassH extends BaseClassA {
    
    // Setter rule 1: at least one argument + no return-type definition
    #[Property('method_name_1')]
    protected function methodName1($val) {/*...*/}
    
    // Setter rule 2: return-type is "void" or "never"
    #[Property('method_name_2')]
    protected function methodName2(): void {/*...*/}
    
    // Combination of both rules above is setter as well
    #[Property('method_name_3')]
    protected function methodName3($val): void {/*...*/}
    
}

```

#### Property-Both (Getter + Setter)
 1. If there is **at least 1 argument** + **any return-type except `void` or `never`** - 
    it is `GETTER + SETTER` (both)!

Example:
```php

/**
 * @property-read $method_name
 */
 
use spaf\simputils\attributes\Property;

/**
 * @property $method_name_1
 */
class MyTargetClassI extends BaseClassA {
    
    // Both rule 1: at least 1 argument + any return-type except `void` or `never`
    #[Property('method_name_1')]
    protected function methodName1($value, $call_type): ?string {/*...*/}
    
}

```

**Important:** For all the above - 3 arguments are always supplied to the target method:
 * `$value` - First argument, it brings the value, that user assigned to the property (in case 
   of getter it's `null`)
 * `$call_type` - "get" or "set" string (only those 2!)
 * `$name` - The name of the property

All the above or any part of those could be skipped. In the most cases for setters you need
first argument almost always.

The second argument is relevant only for a single method for "both" getter and setter to
identify what was requested (so you could do `if-else`).

The third argument always caries the name of the property that was called.

**In the most cases you need only first argument for setters!**

#### Property Getter-Setter reference table

For clearer understanding, here the table of those rules

|                                      | No arguments | 1 or more arguments |
|--------------------------------------|:------------:|:-------------------:|
| No return-type                       |    Getter    |       Setter        |
| return-type except `void` or `never` |    Getter    |        Both         |
| return-type `void` or `never`        |    Setter    |       Setter        |


----

## PropertyBatch attribute

**Important:** `PropertyBatch` works well, but it might be not as much polished as `Property`.

### Basics of PropertyBatch
`PropertyBatch` attribute works similarly to `Property`.
`PropertyBatch` can be used for methods and statically defined fields.

For example:

```php

/**
 * @property-read $method_name
 */
 
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\Math;

/**
 * @property $var1
 * @property $var2
 * @property $var3
 * @property $var4_with_default
 * @property $var5
 * @property $var6
 * @property $var7
 * 
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 */
class MyTargetClassJ extends BaseClassA {
    
    // statically defined field with PropertyBatch
    #[PropertyBatch]
    protected $multiple_variables_definition = [
        'var1', 'var2', 'var3',
        'var4_with_default' => 'This is default value 1',
        'var5' => 'This is default value 2',
        'var6' => 'This is default value 3',
        'var7' => 100500,
    ];
    
    // method generating a batch of variables dynamically
    #[PropertyBatch]
    protected function methodDefinitionMultipleVars() {
        $res = [];
        foreach (Math::range(10, 100, 10) as $i) {
            $res["d_var_{$i}"] = $i;
        }
        
        return $res;
    }
    
}

```

As you could see above, there are 2 simple examples of defining Properties in Batch.
Both works the same way, they should return/contain array or [Box](boxes-arrays.md).

If element of that array has a numerical/integer index - then value would be treated as
a property name, and the default value of it would be `null`.
```php
[
    'my_var_name', 
    'another_var_name', 
    3 => 'the_same_another_var_name',
]
```

If element of that array has a string index (assoc) - then the `key` would be treated as
a property name, and the default value of it would be `value`.
```php
[
    'my_var_name' => null, 
    'another_var_name' => 'default-value-here!', 
    'the_same_another_var_name' => 'test test test',
]
```

Both of those approaches can be mixed together:

```php
[
    'my_var_name_1', 'my_var_name_2', 'my_var_name_3',
    
    'another_var_name' => 'default-value-here!', 
    'the_same_another_var_name' => 'test test test',
    
    3 => 'the_var_name',
]
```

----

`PropertyBatch` has 2 optional arguments:
```
public ?string  $type = null,
public ?string  $storage = null,
```

`$type` - is the same as for [Property](#Property-attribute-arguments) that defines if 
it's a **read-only** or **write-only** or **read-write**. Even constant values would be 
the same (`get`, `set`, `both`).

`$storage` - This is a rare parameter, basically it should be defined into 
`PropertyBatch::STORAGE_SELF` or "#SELF" string if the param names and values must 
be stored inside of the object itself (if they are inherited from 
[ArrayObject](https://www.php.net/manual/en/class.arrayobject.php)). 
That allows to synchronize fields with "array-alike access". 
Good example of it is `PhpInfo` class, access to values can be done 
through `$obj->field_name` and/or `$obj['field_name']`.

**Important**: Mainly, this is reasonable only for some rare cases, when you want 
to process object through `foreach` or any other iterating-mechanics using "array-alike 
access".


----

That's it about `PropertyBatch`.

**Important:** `PropertyBatch` does not identify "read-only", "write-only" or "read-write"
type by the signature of a method. 
**The type should be explicitly defined, or omitted** (If omitted then "read-write"/"both"
considered)


## Debugging and printing out

**Note:** `pr()` can be considered as `print_r()` (not always, but for the examples 
it would be enough).

Properties have some additional functionality that could be comfortably used for debugging.

For example if you would print out an object with `pr()`, `prstr()`, `pd()` or `print_r()` (there are
some bug related exceptions like 
[Native PHP objects bug](php-edges.md#Native-PHP-objects-bug))

```php

class BaseClassA extends SimpleObject {

}

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
class MyTargetClassB extends BaseClassA {

	public $my_sd_field = 'Ho Ho Ho';

	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

Output would be:
```php

MyTargetClassB Object
(
    [d_var_10] => 10
    [d_var_20] => 20
    [d_var_30] => 30
    [d_var_40] => 40
    [d_var_50] => 50
    [d_var_60] => 60
    [d_var_70] => 70
    [d_var_80] => 80
    [d_var_90] => 90
    [d_var_100] => 100
    [year] => 2022
    [my_password] => JJ-b23/ioio+9090
    [obj_id] => 117
    [obj_type] => MyTargetClassB
    [my_sd_field] => Ho Ho Ho
    [reindeer] => Array
        (
            [0] => Dasher
            [1] => Dancer
            [2] => Prancer
            [3] => Vixen
            [4] => Comet
            [5] => Cupid
            [6] => Donner
            [7] => Blitzen
            [8] => Rudolph
        )

    [cat] => Tom
)

```

As you can see, the output is really comfortably displaying all the fields of the object
including virtual properties.

The only problem  that it outputs some fields and their values, that can be highly unsafe.
For example `$my_password` field.

Besides that, if value is related to IO uncached/uncacheable stuff like files, or streams 
content, etc. - we would have a problem that every debugging would read content of that
virtual field, and including the content (which can be HUGE!) into the output.

### DebugHide attribute

The attribute `\spaf\simputils\attributes\DebugHide` can be applied
to any field (virtual and/or statically defined) and even 
to the whole class.

#### Hiding the whole field

**Important:** Be careful, you might hide a field, and forget that it
is exists there, but it will be there anyways. Hiding the whole field
can cause some troubles, be careful!

#### Usage of DebugHide
Just apply it to a field you want to hide completely:

```php


/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
class MyTargetClassB extends BaseClassA {

	public $my_sd_field = 'Ho Ho Ho';

	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

    #[DebugHide]
    #[Property('year')]
    protected function pseudoName(): int {
        return 2022;
    }

	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

The output would be:
```php

MyTargetClassB Object
(
    [d_var_10] => 10
    [d_var_20] => 20
    [d_var_30] => 30
    [d_var_40] => 40
    [d_var_50] => 50
    [d_var_60] => 60
    [d_var_70] => 70
    [d_var_80] => 80
    [d_var_90] => 90
    [d_var_100] => 100
    [my_password] => JJ-b23/ioio+9090
    [obj_id] => 117
    [obj_type] => MyTargetClassB
    [my_sd_field] => Ho Ho Ho
    [reindeer] => Array
        (
            [0] => Dasher
            [1] => Dancer
            [2] => Prancer
            [3] => Vixen
            [4] => Comet
            [5] => Cupid
            [6] => Donner
            [7] => Blitzen
            [8] => Rudolph
        )

    [cat] => Tom
)

```

As you can see, the output does not have a field `$year` which is still accessible in the code.

But if we want to hide `$my_password` value, but we don't want to hide the whole field?

Simple enough. Just specify the first parameter (`$hide_all`) to the attribute as `false`:

```php

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
class MyTargetClassB extends BaseClassA {

	public $my_sd_field = 'Ho Ho Ho';

	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[DebugHide]
	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[DebugHide(false)]
	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

The output would be like this:

```php

MyTargetClassB Object
(
    [d_var_10] => 10
    [d_var_20] => 20
    [d_var_30] => 30
    [d_var_40] => 40
    [d_var_50] => 50
    [d_var_60] => 60
    [d_var_70] => 70
    [d_var_80] => 80
    [d_var_90] => 90
    [d_var_100] => 100
    [my_password] => ****
    [obj_id] => 117
    [obj_type] => MyTargetClassB
    [my_sd_field] => Ho Ho Ho
    [reindeer] => Array
        (
            [0] => Dasher
            [1] => Dancer
            [2] => Prancer
            [3] => Vixen
            [4] => Comet
            [5] => Cupid
            [6] => Donner
            [7] => Blitzen
            [8] => Rudolph
        )

    [cat] => Tom
)

```

The `$my_password` field now displayed, but it's value is hidden!
Additionally if you specify the second param to non-empty value, the `****` string would be
replaced with it:

```php

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
class MyTargetClassB extends BaseClassA {

	public $my_sd_field = 'Ho Ho Ho';

	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[DebugHide]
	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[DebugHide(false, ' ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ ')]
	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

The output would be:

```php

MyTargetClassB Object
(
    [d_var_10] => 10
    [d_var_20] => 20
    [d_var_30] => 30
    [d_var_40] => 40
    [d_var_50] => 50
    [d_var_60] => 60
    [d_var_70] => 70
    [d_var_80] => 80
    [d_var_90] => 90
    [d_var_100] => 100
    [my_password] =>  ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ 
    [obj_id] => 117
    [obj_type] => MyTargetClassB
    [my_sd_field] => Ho Ho Ho
    [reindeer] => Array
        (
            [0] => Dasher
            [1] => Dancer
            [2] => Prancer
            [3] => Vixen
            [4] => Comet
            [5] => Cupid
            [6] => Donner
            [7] => Blitzen
            [8] => Rudolph
        )

    [cat] => Tom
)

```

As you can see the shadowing string `****` 
is replaced with ` ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ `.

----

Till now we were hiding only virtual `Property` fields, but let's see what happens if you hide
the virtual `PropertyBatch` fields:

```php

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
class MyTargetClassB extends BaseClassA {

	public $my_sd_field = 'Ho Ho Ho';

	#[DebugHide(false)]
	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[DebugHide]
	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[DebugHide]
	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[DebugHide(false, ' ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ ')]
	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

And the output would be:

```php

MyTargetClassB Object
(
    [my_password] =>  ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ 
    [obj_id] => 117
    [obj_type] => MyTargetClassB
    [my_sd_field] => Ho Ho Ho
    [reindeer] => ****
    [cat] => ****
)

```

As minimum all the `$d_var_*` fields disappeared!
But in addition to that - Reindeer and Cat are classified now!

So the main logic - it does hide all of the underlying virtual fields.

**Important:** For now, the statically defined array of field names for `PropertyBatch` are always
hidden. Maybe it will change, but most likely - it will not change at all!

----

In regard to statically defined simple fields - it just works exactly the same:

```php

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
class MyTargetClassB extends BaseClassA {

	#[DebugHide(false)]
	public $my_sd_field = 'Ho Ho Ho';

	#[DebugHide(false)]
	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[DebugHide]
	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[DebugHide]
	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[DebugHide(false, ' ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ ')]
	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

Output:

```php

MyTargetClassB Object
(
    [my_password] =>  ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ 
    [obj_id] => 117
    [obj_type] => MyTargetClassB
    [my_sd_field] => ****
    [reindeer] => ****
    [cat] => ****
)

```

----

The final thing is to silence the whole class. I don't know why you would
want that, but there it is:

```php

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
#[DebugHide]
class MyTargetClassB extends BaseClassA {

	public $my_sd_field = 'Ho Ho Ho';

	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[DebugHide(false)]
	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

Output would be:

```php

MyTargetClassB Object
(
)

```

**Important:** It can be dangerous to do it this way, so be careful and
do not accidentally trick yourself of the object being "empty" when
it's not!

Much better approach is using `false` as first argument as minimum:

```php

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
#[DebugHide(false)]
class MyTargetClassB extends BaseClassA {

	#[DebugHide(false)]
	public $my_sd_field = 'Ho Ho Ho';

	#[DebugHide(false)]
	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[DebugHide]
	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[DebugHide]
	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[DebugHide(false, ' ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ ')]
	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

The output would be:
```php

MyTargetClassB Object
(
    [0] => ****
)

```

And the very last example with the custom text:
```php

/**
 * This comment property hinting is a good practice, but is not necessary for the functionality
 * @property-read int $year
 * @property array $reindeer
 * @property string $cat
 *
 * @property $d_var_10
 * @property $d_var_20
 * @property $d_var_30
 * @property $d_var_40
 * @property $d_var_50
 * @property $d_var_60
 * @property $d_var_70
 * @property $d_var_80
 * @property $d_var_90
 * @property $d_var_100
 * @property $my_password
 */
#[DebugHide(false, '--- CLASS IS SILENCED ---')]
class MyTargetClassB extends BaseClassA {

	#[DebugHide(false)]
	public $my_sd_field = 'Ho Ho Ho';

	#[DebugHide(false)]
	#[PropertyBatch]
	protected $different_stuff = [
		'reindeer' => [
			'Dasher', 'Dancer', 'Prancer', 'Vixen',
			'Comet', 'Cupid', 'Donner', 'Blitzen',
			'Rudolph',
		],
		'cat' => 'Tom',
	];

	#[DebugHide]
	#[PropertyBatch]
	protected function test() {
		$res = box();
		foreach (Math::range(10, 100, 10) as $i) {
			$res["d_var_{$i}"] = $i;
		}

		return $res;
	}

	#[DebugHide]
	#[Property('year')]
	protected function pseudoName(): int {
		return 2022;
	}

	#[DebugHide(false, ' ~~~ YOU SHALL NOT SEE MY PASSWORD! ~~~ ')]
	#[Property('my_password')]
	protected function myPassword(): string {
		return 'JJ-b23/ioio+9090';
	}
}

$obj = new MyTargetClassB();

pr($obj);

```

Output would be:
```php

MyTargetClassB Object
(
    [0] => --- CLASS IS SILENCED ---
)

```
