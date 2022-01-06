[< Back to README.md](../README.md)

----

# Properties

## Intro

Properties are "object-variables" that can store data related to a particular object.
Usually "property" term is an alias of "field", but in the framework there is 
a following terms definition:
 * Under **properties** or **Dynamically defined properties** meant "object-variables" 
   with help of `\spaf\simputils\attributes\Property` or `\spaf\simputils\attributes\PropertyBatch`.
   (Basically getter-setter ones)
 * **Statically defined properties** can be used to refer to "object-variables" that
   are opposite to **properties** (**Dynamically defined properties**)
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

All the examples above used 2 arguments each time for Property definition.
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

#### Getter
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
in the indication of the method type. **Only "method signature" matter for that!**

#### Setter
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

#### Both (Getter + Setter)
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
    protected function methodName1($val, $type): ?string {/*...*/}
    
}

```

**Important:** For all the above 3 arguments are always supplied to the target method:
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

#### Reference table

For clearer understanding, here the table of those rules

|                                      | No arguments | 1 or more arguments |
|--------------------------------------|:------------:|:-------------------:|
| No return-type                       |    Getter    |       Setter        |
| return-type except `void` or `never` |    Getter    |        Both         |
| return-type `void` or `never`        |    Setter    |       Setter        |


// TODO Proceed here about:
// DebugHide, `pr()` and `print_r()`
