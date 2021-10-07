## Version object
Version object represents the information about software version in a form of an object, allowing to do some operations like
"version string parsing", "comparison", "sorting", "easy conversion to string", etc.

The default version-parser mechanism allows to parse the most common variations of [Semantic Versioning](https://semver.org) format.
It's strongly advised to follow the semantic versioning format.

In case of necessity, you can change the parsing infrastructure with other implementations or your very own custom mechanism.
By default `DefaultVersionParser` parser is being used. At the section [Customize parsing](#Customize_parsing) you can find more details about
parser customization.

### Existing simputils version parsers
 1. `spaf\simputils\versions\DefaultVersionParser` - Default version parser
 2. `spaf\simputils\versions\MapVersionParser` - Mapping versions parser (undone)
 3. `spaf\simputils\versions\DebVersionParser` - DEB package (.deb) version parser (undone)
 4. `spaf\simputils\versions\RpmVersionParser` - RPM package (.rpm) version parser (undone)

### Basic usage

```php
    use spaf\simputils\models\Version;
    
    // The second parameter is optional, but it's recommended to always specifying it
    $php_version = new Version(phpversion(), 'PHP');
    // Output as a string would be:
    echo "{$php_version}\n";
    // Output as an object would be:
    print_r($php_version);
    // Get particular component of the version string (if parsed correctly)
    echo "Full version: {$php_version}\n";
    echo "Type is: {$php_version->build_type}; Rev is: {$php_version->build_revision}\n";
    echo "Major is: {$php_version->major}\n";
    echo "Minor is: {$php_version->minor}\n";
    echo "Patch is: {$php_version->patch}\n";
```

Then the output as the string would be:
```
8.1.0-RC2
```

And the output as the object would be:
```
spaf\simputils\Version Object
(
    [software_name] => PHP
    [parsed_version] => 8.1.0-RC2
)
```

Particular component of the version string (if parsed correctly):
```
Full version: 8.1.0-RC2
Type is: RC; Rev is: 2
Major is: 8
Minor is: 1
Patch is: 0
```

## Version extended usage

### Customize parsing
There are 2 different ways to specify custom parser class for the Version objects. [A global one](#Global_parser), and [a local one](#Local_parser).

#### Global parser
Global parser will affect every newly created object (but only before the new object creation).

The global parser can be specified/accessed directly through the static property `Version::$default_parser_class`. 
By default, it uses `spaf\simputils\versions\DefaultVersionParser` class. If you want to change it to another one, or
your custom one - you need to run the following code as early as possible:

```php
    use spaf\simputils\versions\DefaultVersionParser;
    use spaf\simputils\models\Version;
    
    // Instead of DefaultVersionParser use your another/custom class.
    Version::$default_parser_class = DefaultVersionParser::class;
```
After that, every new `Version` object will be created with that parser object.

#### Local parser
The local parser - is the parser object that is being used inside of the exact created `Version` object.

The `Version::$default_parser_class` is being used during the new `Version` object creation, and after that,
the `Version` object uses the parser of that type.

Here is an example:
```php
    use spaf\simputils\versions\DefaultVersionParser;
    use spaf\simputils\models\Version;
    
    class CustomParser extends DefaultVersionParser {
        // Your own custom parser
    }
    
    $php_version = new Version(phpversion(), 'PHP');
    $php_version->parser = new CustomParser;
    echo get_class($php_version->parser);
```

**Important:** The `->parser` property must contain the object, __not the class__. 
This is why we are using `new` in the code.

The output would be:
```
CustomParser
```

While the new object will still be created with the default parser object in it:
```php
    use spaf\simputils\versions\DefaultVersionParser;
    use spaf\simputils\models\Version;
    
    class CustomParser extends DefaultVersionParser {
        // Your own custom parser
    }
    
    $php_version = new Version(phpversion(), 'PHP');
//    $php_version->parser = new CustomParser;
    echo get_class($php_version->parser);
```

The output would be (unless you change global parser class):
```
spaf\simputils\versions\DefaultVersionParser
```

#### Implementation of a custom parser
There are at least "2.5" ways to implement your custom parser.

----

**The first one** is the simplest inheritance of your custom class through `spaf\simputils\versions\DefaultVersionParser` 
(or any other non-abstract class for that purpose). In this case the default functionality will be in place for you,
and you can just redefine needed parts. In the most cases it's the cleanest and the mostly suggested way of the implementation.

For example, you are willing to change only the string representation of the version, so you override it like this:

```php

use spaf\simputils\models\Version;
use spaf\simputils\versions\DefaultVersionParser;

class MyVersionParser extends DefaultVersionParser {

    public function toString(Version $obj) : string {
        return "<{$obj->major}/{$obj->minor}/{$obj->patch}>";
    }
    
}

// then in the entry point file:

Version::$default_parser_class = MyVersionParser::class;

```

After the defined above code the version objects will use new string representation functionality:
 * `1.2.3-RC4` would be output as `<1/2/3>`
 * `22.0.0-RC4` would be output as `<22/0/0>`
 * `1.1.90` would be output as `<1/1/90>`

----

**The second one** is through implementing on top of the inherited abstract class `spaf\simputils\components\BasicVersionParser`.
In this case you will have to implement __almost ALL__ the logic by yourself. If the first way does not fit to the architecture
of your software, you can use the abstract `BasicVersionParser` to implement from scratch your logic.

**Important:** Class `Version` and classes of `*VersionParser` having similarly named methods, keep in mind that
in different class group it can have different meaning. Parser classes usually contain logic, while Version class just shortcutting
the functionality into the parser's corresponding method.

The following 3 methods must be implemented in your class: `parse()`, `greaterThan()` and `equalsTo()`. 
The `parse()` method is being used by the version object to parse incoming string, so the method have to return array
containing following keys and corresponding values:
 * `major` - Must contain **integer** of **major** version
 * `minor` - Must contain **integer** of **minor** version
 * `patch` - Must contain **integer** of **patch** version
 * `prefix` - Can contain **string** of textual prefixing part of the parsed string (before the version part in the string)
 * `postfix` - Can contain **string** of textual postfixing part of the parsed string (after the version part in the string)
 * `build_type` - Can contain **string** of the following values ([more about values meaning](https://www.php.net/manual/en/function.version-compare.php)):
   1. `DEV`
   2. `A` or `ALPHA`
   3. `B` or `BETA`
   4. `RC`
   5. `#`
   6. `P` or `PL`
 * `build_revision` - Can contain **integer** of a build revision (integer value right after the `build_type` value in the unparsed string)

Interesting part, that you don't necessary need to implement all the sorting functionality like `lessThan()` and etc. 
Because the minimum implementation of `greaterThan()` and `equalsTo()` can be enough to perform other types of
comparison. Though, important to note, it's recommended to implement all the sorting related methods if necessary, due to possible
inefficiency in different cases (inefficiency is subjective here, but possible in case of databases, APIs, etc.).


Example:

```php
use spaf\simputils\components\BasicVersionParser;

class MyVersionParser extends BasicVersionParser {
    
    public function parse(Version $version_object, ?string $string_version): array {
        return [
            'major' => 1,
            'minor' => 2,
            'patch' => 3,
            'prefix' => null,
            'postfix' => null,
            'build_type' => 'RC',
            'build_revision' => 2,
        ];
        // This array would mean version like this: "1.2.3-RC2"
    }

    public function greaterThan(Version $obj1, Version $obj2): bool {
        // This is just an example
        return true;
    }
    
    public function equalsTo(Version $obj1, Version $obj2): bool {
        // This is just an example
        return true;    
    }
    
}
```

----

**The last 0.5 one** is the most abstract you can get - implementation through 
the class interface `spaf\simputils\interfaces\VersionParserInterface`. 
In this case you will have to implement __ALL__ the logic by yourself.

Through the interface, you will have to implement all the methods of the interface.

```php
use spaf\simputils\interfaces\VersionParserInterface;

class MyVersionParser implements VersionParserInterface {

    public function parse(Version $version_object, ?string $string_version): array {
        // Must be implemented
    }
    
    public function greaterThan(Version $obj1, Version $obj2): bool {
        // Must be implemented
    }
    
    public function greaterThanEqual(Version $obj1, Version $obj2): bool {
        // Must be implemented
    }
    
    public function equalsTo(Version $obj1, Version $obj2): bool {
        // Must be implemented
    }
    
    public function lessThan(Version $obj1, Version $obj2): bool {
        // Must be implemented
    }
    
    public function lessThanEqual(Version $obj1, Version $obj2): bool {
        // Must be implemented
    }
    
    public function toString(Version $obj): string {
        // Must be implemented
    }
    
}
```

#### Comparing objects/versions

Different variants of comparison:

```php
   use spaf\simputils\models\Version;
   
   $app_name = 'App Doe';
   $v1 = new Version('1.2.3', $app_name);
   $v2 = new Version('2.0.1', $app_name);
   $v3 = new Version('01.02.03', $app_name);


```

#### Sorting