[<< Back to README.md](../README.md)

----

# Advanced PHP Info Object

Everything is really simple with it.
It's an array-like (box-like) object that contains almost complete PHP Info data.
That you can access and walk through any comfortable for you way. It also compatible
with the common IDE autocomplete (only top level fields).

You can access top-level fields (those that directly on the object):
 1. In a property/field-like style:
    ```php
    use spaf\simputils\PHP;
    $phpi = PHP::info();
    echo "{$phpi->cpu_architecture}";
    ```
 2. In an array-like style (box functionality is also available):
    ```php
    use spaf\simputils\PHP;
    $phpi = PHP::info();
    echo "{$phpi['cpu_architecture']}";
    ```
 3. Iterate over the object:
    ```php
    use spaf\simputils\PHP;
    $i = 0;
    foreach (PHP::info() as $k => $v) {
        echo "{$k} ====> {$v}\n";
        if ($i++ > 4) {
            // Just a small limiter
            break;
        }
    }
    ```
 
## Additional benefits
 1. All the versions are wrapped into `Version` class (out of the box version comparison, etc.)
 2. The object is created once, and can be accessed through `PHP::info()` 
    (manually possible to have multiple)
 3. The object is being derivative from Box, that means that it has all the benefits (
    all the underlying arrays are Boxed as well, so the whole content of the php info 
    is available through Box functionality)
 4. Contains lots of information, and probably will be extended in the future with more
    relevant information.

## Full output example

Full output example you can find here: [PHP Info Full Output Example](php-info-full-example.md)

## Reasoning to use Advanced PHP Info Object
The native `phpinfo()` returns just a static text representation, which is incredibly 
uncomfortable to use.
Info about native one you can find here: https://www.php.net/manual/ru/function.phpinfo.php



