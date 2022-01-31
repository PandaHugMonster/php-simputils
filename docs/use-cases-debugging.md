[<< Back to README.md](../README.md)

----

## In-place quick debugging or PleaseDie

**Important:** `pd()` is a shortcut for `PHP::pd()`. In the most cases is recommended to use 
a shortcut.

This is simple dev/debugging method like "alert()" of JavaScript browser implementation. It 
prints out the data/vars and then stops the execution.

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

### Extension and Redefinability
Please Die functionality could be extended or even redefined. By another framework, or by 
another library, or by yourself.

A good example is a small "proxy-lib" 
[spaf/yii2-simputils](https://github.com/PandaHugMonster/yii2-simputils) that redefines the pd() 
underlying functionality with a better outputting mechanism like "VarDumper".

Even though through this "proxy-lib" you redefine functionality, you don't need to change an import 
and a call of "pd()" anywhere in your code, because the call stays the same, but the different 
functionality is being used.
