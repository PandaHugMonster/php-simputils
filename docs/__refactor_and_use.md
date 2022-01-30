



6. [Examples](#Examples)
    1. [InitConfigs and bootstrapping process](#InitConfigs-and-bootstrapping-process)
    2. [Properties - Getters and Setters](#Properties--Getters-and-Setters)
    3. [Working with files](#Working-with-files)
    4. [Version objects and working with versions](#Version-objects-and-working-with-versions)
    5. [Advanced PHP Info object](#Advanced-PHP-Info-object)
    6. [DotEnv and Env Vars](#DotEnv-and-Env-Vars)
    7. [Boxes or advanced arrays](#Boxes-or-advanced-arrays)
    8. [Advanced Date and Time](#Advanced-Date-and-Time)
7. [Further documentation](#Further-documentation)

-----------------


----
What has left to do with the documentation:

* [x] InitConfig and bootstrapping
* [ ] Per helper description:
    * [ ] `System`
    * [ ] `Str`
    * [ ] `PHP`
    * [ ] `Math`
    * [ ] `FS`
    * [x] `DT`
    * [ ] `Data`
    * [ ] `Boolean`
* [ ] Files infrastructure
    * [ ] `File` object
    * [ ] File Processors
* [ ] Integrated DotEnv functionality
    * [ ] Comment DotEnv Extensions
* [ ] Adjust and connect Version description
* [ ] Adjust and connect PhpInfo description
* [x] Adjust and connect DateTime description
* [ ] Adjust and connect Box description
    * [ ] `filter` method
    * [ ] `each` method
* [ ] Short intro into GitRepo
* [ ] Architecture + Guidelines
* [ ] Build a md-documents-map
* [ ] Remove all the obsolete documents
* [ ] Clear up all the obsolete leftovers
* [ ] Add highlights on the main page

----


## Examples

_In this section will be shown examples and benefits of the architecture_

**Important:** Not all the benefits and useful perks might be demonstrated on this page.
Please refer to the corresponding page of each component, or Ref API pages.


### InitConfigs and bootstrapping process

**The main app**
: Your target application (not a submodule or a library)

**A sub app**
: Library, sub-module, external code package

Bootstrapping of the framework is called init/initialization.

It happens when called `PHP::init()`. It returns reference to the `InitConfig` object.

Like this:

```php
use spaf\simputils\PHP;

PHP::init();
// or
$config = PHP::init();
```

----

The example above is valid only for **the main app**

**Important:** More about initialization of **a sub app** you can find here:
[Init for external modules and libraries]()

----

Proceeding with `PHP::init()` in **the main app**. The init process is not required for
the most of utils and models, but can significantly improve development experience and operation.

For example DotEnv functionality is activated by default as `InitBlock`.

**InitBlock**
: The functionality attached to the `InitConfig`, that should be initialized, when that
`InitConfig` will be initialized. Basically switchable "plugins" or "extensions"


There are 2 ways of specifying InitConfigs.

First method is just to provide an array with key-value pair of fields for the InitConfig.

Like that:

```php

use spaf\simputils\PHP;

PHP::init([
    'a_param_1' => 'a value content 1',
    'a_param_2' => 'a value content 2',
    'a_param_3' => 'a value content 3',
    
    /* ... */
]);

```

In this case default `InitConfig` class will be used as an object, and this array of params
will be applied to it. This is the simplest configuration you can do.

Another way is to explicitly assign the InitConfig object by yourself. This is preferred way,
because it's highly intuitive and flexible option. You just create your own class extended
from `InitConfig`, and then redefine all the stuff you want!

Example:

```php

use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;

class MyCustomInitConfig extends InitConfig {

    // IMP  the code below will remove all the default InitBlocks' init process.
    //      This will disable as minimum DotEnv functionality!
    public null|array|Box $init_blocks = [];
}


PHP::init(new MyCustomInitConfig);

```

At any point of time you can receive the config by this command (for **the main app**
init config object):

```php

use spaf\simputils\PHP;

$config = PHP::getInitConfig();

```

This way you can have an access to the InitConfig object that is being used.

### InitBlocks or subroutines

Example of an InitBlock implementation: `\spaf\simputils\components\initblocks\DotEnvInitBlock`

The InitBlock class could be defined by implementing interface
`\spaf\simputils\interfaces\InitBlockInterface`.
And after that you could just provide a new object to the config array like that:

```php

use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\PHP;
use function spaf\simputils\basic\env;
use function spaf\simputils\basic\pr;

class MyInitBlock implements InitBlockInterface {

    public function initBlock(BasicInitConfig $config): bool {
        // This code will be initialized during `PHP::init()` call
        // This command will add environmental variable "MY_SPECIAL_ENV_VARIABLE" 
        PHP::envSet('MY_SPECIAL_ENV_VARIABLE', 'Pandas love bamboo!', true);
        return true;
    }

}


// Mind the array brackets
PHP::init([ new MyInitBlock ])

// IMP  At this point if our custom InitBlock class was successfully initialized
//      we will have access to "MY_SPECIAL_ENV_VARIABLE" env variable!

pr(env('MY_SPECIAL_ENV_VARIABLE'));

```
The code above will output: `Pandas love bamboo!`


**Important:** This short syntax is preferable, but requires the definition of InitBlock
objects directly into the config-init array (This syntax will work only with real objects,
not class strings, and only for `PHP::init([])`), that InitBlock must
implement `\spaf\simputils\interfaces\InitBlockInterface`.


You can create as much such InitBlocks as you want. Just remember, all of them will be ran
for each request... So if you are working with another framework, you should use their
bootstrapping mechanisms. In case of Yii2 you should follow this one:
https://www.yiiframework.com/doc/guide/2.0/en/runtime-bootstrapping

If you use initially just the SimpUtils framework, then of course you could use this
InitConfig process. Just remember that this can lead to drastically under-performing solution
of yours.

If you ask a question: "Then why would we want it, if we have such functionality in our
preferred framework like Yii2, laravel, etc."?

The answer would be: SimpUtils is a micro-framework, self-sufficient more or less, and
it can be ran before any of your framework initialization/bootstrap process to provide more
comfortable usage of your framework, even on the early stage of configuration.

For example, in case of Yii2, in the "config" of your web-app, you operate with "plain"
references to classes and components, and config stage is done before bootstrapping process.
And if you would like to use quick access to ".env" variables inside of your Yii2 config
file - you will not be able to do that easily.

**So the SimpUtils initialization/bootstrapping mechanisms are early-stage mechanisms**.

Besides that, if you have to work raw without a big framework, you would have to implement
your own bootstrapping mechanisms. And to spare some time, much easier to use this
low-level mechanism of SimpUtils.


#### Overall architecture of initialization process

Initialization process of SimpUtils is modular, with a single entry-point.

**The main app** calls `PHP::init()`, this is a single entry point. No module should
try to run it, in case if it's done outside of **the main app**, then that module must
be considered unsafe.

But every **sub app** (module) can register their very own InitConfig.
For that purpose they have to specify a unique name (usually own-module-name) for the
`PHP::init()` call like this:

```php

/* ... IMP  this is code of an external library or module, not the main app! */

use spaf\simputils\generic\SubAppInitConfig;
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;

class MyModCodeExampleInitConfig extends SubAppInitConfig {

    // Make sure this is a reasonable and unique name
    public ?string $name = 'my-mod-code-example-default-INIT';
    public ?string $code_root = __FILE__;
    public ?string $working_dir = '/tmp/my-module-working-directory';

}


// IMP  The $name, $code_root and/or $working_dir can be defined in the default value of
//      your config, or redefined during the call
PHP::init(new MyModCodeExampleInitConfig)

/* ... */

```

At this point config for that name is registered with this config object.
You can get the config object at any point ("not recommended", but yes, even outside of your
code stub):

```php

use spaf\simputils\PHP;

$module_config = PHP::getInitConfig('my-mod-code-example-default-INIT');


// Here you can now access the location of the configs, etc.
echo $module_config->working_dir;

```

The example above is really cool for the modular development, that each **sub app** can
rely on it's own init-config with own "code_root" and "working_dir"!

And the same time all of them can rely on each other's init config simply specifying name
to `PHP::getInitConfig()` method.

**Important:** When you are not specifying name or you use "app" name - it always refers to
the main app.

**Important:** The name "app" - is registered special name that means **the main app**, so
it must not be used (empty name as well refers to "app").

-----

The example above is awesome, but it will not be automatically ran due to security reasons,
so **the main** InitConfig has to explicitly specify InitBlock of your module, that will
register your module's InitConfig. It seems a bit overwhelming, but it's not that difficult.

So in the most cases if you develop **sub app** (module/lib/extension) you just need
to create 1 class extending it from `SubAppInitConfig` and then a user of **the main app**
has to creat an object of that class of yours and provide it to and array of arguments of
InitConfig (or add it to `init_blocks` array manually).

Here is an example:

```php

use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\SubAppInitConfig;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;

// Here is module defined classes
class MyInitConfig extends SubAppInitConfig {
    // Make sure this is a reasonable and unique name
    public ?string $name = 'my-mod-ule';
}


/////////////// Below goes code in the main app entry-point (outside of sub app)


PHP::init([ new MyInitConfig ]);

```

If you would want to do your additional initialization, just override the `init()` method in
the class (don't always forget to run `parent::init()` in the end:

```php

use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\SubAppInitConfig;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;

// Here is module defined classes
class MyInitConfig extends SubAppInitConfig {
    // Make sure this is a reasonable and unique name
    public ?string $name = 'my-mod-ule';
    
    public function init(){
        
        pd('Hello World, and die.... ^_^');
        
        parent::init(); // TODO: Change the autogenerated stub
    }
    
}


/////////////// Below goes code in the main app entry-point (outside of sub app)


PHP::init([ new MyInitConfig ]);


```

**Important:** This way you can do infinite hierarchy of initialization. Though, just always
keep in mind that this hierarchy will be called for an every single request. <mark>So when possible
keep the init/bootstrapping process as ascetic as possible!</mark>

That's it about the initialization process.

Here goes some more examples:

...... ADD EXAMPLES .......


### Properties

Properties are done through concept of "PHP Attributes". A bit more about those you can
read here: https://php.watch/articles/php-attributes

The good part is that we don't need to use any "prefixes" and special "name conventions",
like it was done in the past for example like for "Yii", "Yii2", etc.

More details and examples of properties, you can find here: [Properties](docs/properties.md)

Some examples:

```php
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();
 
/**
 * My Shiny Classy (Example 1)
 * 
 * For this example we inherit from `SimpleObject`, 
 * but no worries - it's not the only way 
 * (about that will be in further examples)!
 * 
 * **Important:** This "property-read" line bellow 
 * is not required, but it's a good practice, 
 * so IDEs could help you with autocompletion.
 * @property-read string $mySpecialMethodName My special property
 */
class MyShinyClassy extends SimpleObject {

    #[Property]
    function mySpecialMethodName(): string {
        return  "Hey Hey! You thought I'm a method?! ".
                "Wrong, I'm a Property!";
    }

}

// Creating object. Don't forget due to PHP limitation
// you can create dynamic properties like above only
// for "non-static" methods
$obj = new MyShinyClassy();

// Now you access your property as a property, not as a method
echo $obj->mySpecialMethodName;

// though you can access it as a method too because it's considered
// as "public" method
echo $obj->mySpecialMethodName();

```

But let's say you don't want to name you property after the method name,
for example in my case I prefer `camelCase` for methods,
but `snake_case` for Properties and Fields.

```php
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();
 
/**
 * My Shiny Classy (Example 2)
 * 
 * For this example we inherit from `SimpleObject`, 
 * but no worries - it's not the only way 
 * (about that will be in further examples)!
 * 
 * **Important:** This "property-read" line bellow 
 * is not required, but it's a good practice, 
 * so IDEs could help you with autocompletion.
 * @property-read string $my_special_method_name My special property
 */
class MyShinyClassy extends SimpleObject {

    // At here we are marking method as "protected",
    // it's a good practice not exposing method name,
    // when the property is available by the property name.
    
    #[Property('my_special_method_name')]
    protected function mySpecialMethodName(): string {
        return  "Hey Hey! You thought I'm a method?! ".
                "Wrong, I'm a Property!";
    }

}

// Creating object. Don't forget due to PHP limitation
// you can create dynamic properties like above only
// for "non-static" methods
$obj = new MyShinyClassy();

// Now you access your property as a property, not as a method
echo $obj->my_special_method_name;

// This one will not work out anymore, because method is "protected"
echo $obj->mySpecialMethodName();

```

How does the code understand that we are making "getter"? and how do we create a "setter"?


```php
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();
 
/**
 * My Shiny Classy (Example 3)
 * 
 * For this example we inherit from `SimpleObject`, 
 * but no worries - it's not the only way 
 * (about that will be in further examples)!
 * 
 * **Important:** This "property" line bellow 
 * is not required, but it's a good practice, 
 * so IDEs could help you with autocompletion.
 * @property string $my_secret_value My special property
 */
class MyShinyClassy extends SimpleObject {

    protected $_my_secret_value = 'Hello World!';

    // This is our "getter"
    #[Property('my_secret_value')]
    protected function getMySecretValue(): string {
        return $this->_my_secret_value;
    }
    
    // This is our "setter"
    #[Property('my_secret_value')]
    protected function setMySecretValue(string $val): void {
        $this->_my_secret_value = "[ {$val} ]";
    }

}

// Creating object. Don't forget due to PHP limitation
// you can create dynamic properties like above only
// for "non-static" methods
$obj = new MyShinyClassy();

// Getting our value
echo $obj->my_secret_value;
// Output: "Hello World!"

// Setting our value
$obj->my_secret_value = 'Hello Panda!';

// Checking our our value now
echo $obj->my_secret_value;
// Output: "[ Hello Panda! ]"

```

So cool, right?! You can create dynamic properties that would do transparent validation
and stuff!

Wait a second, but how does code understands which code portion is a "setter", and which
is a "getter"?

That is simple as well (maybe not, i idk :) ).

Initially the type of Property is identified by the method signature.

#### GETTER

In a signature ```function getMySecretValue(): string``` there are:
1. Return type is anything but "void" or "never" (not return word in a body of a method!!)
2. No arguments in a signature

So this method is considered as "getter"


#### SETTER

And another signature ```function setMySecretValue(string $val): void```:
1. As minimum one argument is specified
2. Return type only "void" or "never"

So this method is considered as "setter"

#### BOTH or 1 METHOD = 2 WORLDS

What would happen if we would mix up those 2 groups of rules, for example
we would specify `$val` parameter and would specify return type to `string`?

If you would do that, then method would be used for both "GETTER" and "SETTER":
```php
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();
 
/**
 * My Shiny Classy (Example 4)
 * 
 * For this example we inherit from `SimpleObject`, 
 * but no worries - it's not the only way 
 * (about that will be in further examples)!
 * 
 * **Important:** This "property" line bellow 
 * is not required, but it's a good practice, 
 * so IDEs could help you with autocompletion.
 * @property string $my_secret_value My special property
 */
class MyShinyClassy extends SimpleObject {

    protected $_my_secret_value = 'Hello World!';

    // This now "getter" and "setter" at once!
    #[Property('my_secret_value')]
    protected function getMySecretValue(string $val, $type): string {
        if ($type === Property::TYPE_GET) {
            return $this->_my_secret_value;
        }
        
        $this->_my_secret_value = "[ {$val} ]";
    }

}

// Creating object. Don't forget due to PHP limitation
// you can create dynamic properties like above only
// for "non-static" methods
$obj = new MyShinyClassy();

// Getting our value
echo $obj->my_secret_value;
// Output: "Hello World!"

// Setting our value
$obj->my_secret_value = 'Hello Panda!';

// Checking our our value now
echo $obj->my_secret_value;
// Output: "[ Hello Panda! ]"

```

The example above will work the same way as the previous one.

The only difference is to use "2 methods" or "1 method" for Property.

**Important:** Even though you can use 1 method for both, it's commonly recommended
to use 1 method for each part. As minimum because then you can easily control
"read/write"-only functionality (you just comment out the method that you want to block).
But even if you are ok with using "both" version - at least be consistent and use always
this approach at least for "per project" basis.


#### If something goes wrong with a method signature

You always can specify the exact approach with "type" or second param
of `Property` attribute:
```php
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;

// Framework init (recommended, but not mandatory)
PHP::init();
 
/**
 * My Shiny Classy (Example 5)
 * 
 * For this example we inherit from `SimpleObject`, 
 * but no worries - it's not the only way 
 * (about that will be in further examples)!
 * 
 * **Important:** This "property-read" line bellow 
 * is not required, but it's a good practice, 
 * so IDEs could help you with autocompletion.
 * @property-read string $my_secret_value My special property
 * @property-read string $my_secret_value2 My special property
 * @property-read string $my_secret_value3 My special property
 */
class MyShinyClassy extends SimpleObject {

    protected $_my_secret_value = 'Hello World!';

    // This now only "getter" again!
    #[Property('my_secret_value', type: 'get')]
    protected function getMySecretValue(string $val): string {
        return $this->_my_secret_value;
    }

    // The same as above
    #[Property('my_secret_value2', type: Property::TYPE_GET)]
    protected function getMySecretValue2(string $val): string {
        return $this->_my_secret_value;
    }

    // The same as above
    #[Property('my_secret_value3', 'get')]
    protected function getMySecretValue3(string $val): string {
        return $this->_my_secret_value;
    }

}

// Creating object. Don't forget due to PHP limitation
// you can create dynamic properties like above only
// for "non-static" methods
$obj = new MyShinyClassy();

// Getting our value
echo $obj->my_secret_value;
// Output: "Hello World!"

// Would raise an exception, because property is "read"-only
$obj->my_secret_value = 'Hello Panda!';

```

So in the example above you can see that you can enforce method type
as "get", "set" or "both".

**Important:** Though above examples are fully valid, it would be a bit clearer to use
signature without hinting (again, it's not forbidden, use it as you wish! - **more
importantly be consistent per project**)


#### Flexibility and other frameworks compatibility

The "SimpUtils" is a minimal set of flexible perks, so it should be extremely transparent
when using with other libs and frameworks like "laravel", "Yii2", "Zend Framework", etc.

And one of the biggest challenges in this case is to provide functionality above without
locking out user from using their favourite frameworks "base objects".

In PHP multiple inheritance is not allowed, at least directly. So in this case,
you can inherit "layer class" for the favourite framework's "base object", and add
Properties functionality to it through traits.
This way you will have attached Properties functionality
to your favourite framework infrastructure.

**Important:** Because I like `yii2` framework, I develop additional
"yii2-simputils" extension that you can
use directly: https://github.com/PandaHugMonster/yii2-simputils

But for the purpose of example here it is the way you can implement it for your favourite
frameworks:

```php

// Create layer class for your "base object"

use spaf\simputils\attributes\Property;use spaf\simputils\traits\PropertiesTrait;
use spaf\simputils\traits\SimpleObjectTrait;
use yii\base\BaseObject;

/**
 * @property-read $i_am_property_capable_now 
 */
class LayerObject extends BaseObject {
    use SimpleObjectTrait;
    
    // or you can use just Properties functionality, which is not a good idea,
    // because that would cripple capabilities of the SimpUtils framework
    
    // use PropertiesTrait;

    #[Property('i_am_property_capable_now')]
    protected function getIamPropertyCapableNow(): string {
        return "Yep!";    
    }
}

```

That's it! Now inherit all of the classes from it, or create as much "Layer" classes
as you want adding required functionality.


#### How do disable inherited property?

To disable a property - just redefine the method. And do not apply `Property` attribute!

```php

class ExtendedObject extends LayerObject {

    // Redefinition without `Property` attribute,
    // will disable property
    protected function getIamPropertyCapableNow(): string {
        return parent::getIamPropertyCapableNow();
    }
    
    // But the method is still available for the object
    // internal use with the same functionality

}

```

Important to note - redefinition of the method always drops the attribute `Property`.
So if you would want to redefine property's functionality without dropping it,
you will have to specify attribute again for each redefinition.

```php

use spaf\simputils\attributes\Property;

/**
 * @property-read $i_am_property_capable_now
 */
class ExtendedObject extends LayerObject {

    #[Property('i_am_property_capable_now')]
    protected function getIamPropertyCapableNow(): string {
        return 'I am redefined';
    }

}

```

That's it about Properties. There are some more stuff will be described in
the corresponding section about Properties!

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

### Working with Directories

Class `\spaf\simputils\models\Dir` is used for representing system directory.
It works in a similar way as `File`, but has really nice features for working with content
of folders.

**Important:** `Dir` is not inherited from `File`. Both of them are basically independent
classes with some common methods and properties!

The shortcut for a quick directory object creation is: `\spaf\simputils\basic\dr()`

Simple usage:

```php

use function spaf\simputils\basic\dr;


$dir = dr('/usr/lib');

pr("Object is easily casted to absolute path like \"{$dir}\"");

```

Output would be:
```
Object is easily casted to absolute path like "/usr/lib"
```

Besides that you can iterate over the content of it really intuitively:

```php

use spaf\simputils\models\Dir;use function spaf\simputils\basic\dr;use function spaf\simputils\basic\pr;


$dir = dr('/usr/lib');

foreach ($dir as $dir_or_file) {
	if ($dir_or_file instanceof Dir) {
		pr("It's a directory: {$dir_or_file}");
	} else {
		pr("It's a file: {$dir_or_file}");
	}
}


```

Output would be some thing like this (output is stripped):

```
...
It's a directory: /usr/lib/kernel
It's a directory: /usr/lib/klibc
It's a file: /usr/lib/klibc-xcgdUApi-P9SoPhW_fi5gXfvWpw.so
It's a directory: /usr/lib/language-selector
It's a file: /usr/lib/ld-linux.so.2
It's a file: /usr/lib/libBLT.2.5.so.8.6
It's a file: /usr/lib/libBLTlite.2.5.so.8.6
It's a file: /usr/lib/libgimp-2.0.so0
...
```

But this usage is not recursive, what if you want to iterate over all the dirs and sub-dirs.
Besides recursive approach, with this command you can filter out the resulting elements based
on different filters, regexp or even custom filter objects!

```php

use spaf\simputils\components\filters\DirExtFilter;use spaf\simputils\components\filters\OnlyFilesFilter;use function spaf\simputils\basic\dr;

$dir = dr('/usr/lib');

$filters = [
    new OnlyFilesFilter,
    new DirExtFilter(exts: ['ko', 'so'])
];

foreach ($dir->walk(true, ...$filters) as $file) {
    pr("{$file}");
}

```

**Important:** Recursive `walk` can be really slow, it might be good for prototyping and
not deep folder structure, (or for independent microservice that does stuff
independently, and  where speed does not matter much), but for production consider applying
optimizations (non-recursive manual iterations over folders).
The normal non-recursive `walk` **is efficient enough**!


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



    ... OUTPUT IS SO BIG THAT IT WAS REMOVED ...



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

Worth noting that all version information is wrapped into `Version` objects

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
file exists in your working directory (code directory) - those variable will be available
through `$_ENV` or `env()`.

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
//    ... ALL OTHER VARS ARE REMOVED ...
//    
//    [PARAM_1] => 12.2
//    [PARAM_2] => TEST test
// )

// So "PARAM_1" and "PARAM_2" vars of our `.env` are available in your app!

// And to get value of the exact variable:
echo env('PARAM_2');
// Output: "TEST test"

// and
echo env('HOME');
// Output: "/home/ivan"

// Important: Keep in mind, that `env()` for now does not do the normalization, so if
// letter case will not be matching - you will get `null`

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

**IMPORTANT:** There is a significant difference between "php array" and "box".
"box" - is an object, so when it's supplied to a function/method/callable, etc. - it will be passed
by reference, and not copied.

The behaviour is similar to behaviour of "arrays" in python 3.

Example of the situation:
```php

// 2.   When receiving here the supplied box object
//      so the $arg var now is referencing to the original box object, modifying it
//      will cause modification of the original box object.
function myFunc($arg) {
    // $arg is the same object $box
}

$box = box(['my', 'elements', '!']);

// 1.   Passing this box object
myFunc($box);

```

To simulate behaviour of passing by copying you could do this:
```php

// 2.   When receiving here the supplied box object
//      so the $arg var now is referencing to the original box object, modifying it
//      will cause modification of the original box object.
function myFunc($arg) {
    // $arg is the same object $box
}

$box = box(['my', 'elements', '!']);

// 1.   Passing this box object
/** @var \spaf\simputils\models\Box $box 
 */
myFunc($box->clone());
// or
myFunc(clone $box);

```

Example above will provide a copy of the "box" object to the method!

Further examples:

```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\bx;

PHP::init();

// It's just almost exactly as a native PHP array
$b = bx(['my special value', 'another special value']);
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

echo "My B-day is: {$dt->date}\n";
// Output: "My B-day is: 1990-02-22"

// Cool calculations, right?! :D
echo $dt->modify('+6 months -2 days +100 years')->date;
// Output: 2090-08-20

```

**Important:** Currently not everything that is planned - implemented for `DateTime`
object. But in the nearest time it should be improved!

## Further documentation

_UNDONE OR OUTDATED YET_

[docs/use-cases.md](docs/use-cases.md)