### Path-alike Box-array

This is a new feature for `Box` model.

The new short version `Box::pathAlike()` method is available:

```php
PHP::init();

$bx = bx(['TEST', 'PATH', 'alike', 'box'])->pathAlike();

pd($bx, "{$bx}");
```

Output would be:

```text
spaf\simputils\models\Box Object
(
    [0] => TEST
    [1] => PATH
    [2] => alike
    [3] => box
)

TEST/PATH/alike/box
```

Here is the manual way with different examples:

```php
PHP::init();

$b = bx(['TEST', 'PATH', 'alike', 'box']);

pr("{$b}"); // In this case JSON

$b->joined_to_str = true;

pr("{$b}");

$b->separator = '/';

pr("{$b}");

$b->separator = ' ## ';

pr("{$b}");

```

The output would be:

```

["TEST","PATH","alike","box"]
TEST, PATH, alike, box
TEST/PATH/alike/box
TEST ## PATH ## alike ## box

```

### Stretchable feature of Box-array

It works almost exactly as "Path-Alike", but it stringifies boxes including "keys".

Example 1:

```php

PHP::init();

$bx = bx([
    'key1' => 'val1',
    'key2' => 'val2',
    'key3' => 'val3',
    'key4' => 'val4',
])->stretched('=');

pd($bx, "{$bx}");

```

Output would be:

```text
spaf\simputils\models\Box Object
(
    [key1] => val1
    [key2] => val2
    [key3] => val3
    [key4] => val4
)

key1=val1, key2=val2, key3=val3, key4=val4
```

And as it might be obvious already, there is a really good potential to use it
for url params.

Example 2:

```php
PHP::init();

$bx = bx([
	'key1' => 'val1',
	'key2' => 'val2',
	'key3' => 'val3',
	'key4' => 'val4',
])->stretched('=', '&');

// or shorter and more intuitive:

$bx = bx([
	'key1' => 'val1',
	'key2' => 'val2',
	'key3' => 'val3',
	'key4' => 'val4',
])->paramsAlike();

pd($bx, "{$bx}");

```

Output would be:

```text
spaf\simputils\models\Box Object
(
    [key1] => val1
    [key2] => val2
    [key3] => val3
    [key4] => val4
)

key1=val1&key2=val2&key3=val3&key4=val4
```

Important to note, this methods does not turn the objects directly to strings!
They store in the object special configuration, that when you start
to stringify this Box - it will use the saved settings for that.

#### Value wrappers and htmlAttrAlike()

For html attrs alike just use this method:

```php
$bx = bx([
	'data-my-attr-1' => 'test',
	'data-my-attr-2' => 'test2',
])->htmlAttrAlike();
// You can specify first argument " or ' to control which wrapper symbols are used.
// Or you could even specify callable to pre-process and wrap value automatically!
```

Output would be:

```text
data-my-attr-1="test" data-my-attr-2="test2"
```

But if you would want to do "value-processing" instead of just wrapping, you could use
stretched functionality:

```php
$bx = bx([
	'data-my-attr-1' => 'test',
	'data-my-attr-2' => 'test2',
])->stretched(' = ', ' ', function ($val, $key, $bx) {
	return "(`{$val}`)";
});
```

Output would be:

```text
data-my-attr-1 = (`test`) data-my-attr-2 = (`test2`)
```

#### Wrap, wrap, wrap

For stretching functionality you can wrap each part separately
with `$value_wrap` and `$key_wrap`. They work in the same way, but wrap their each
corresponding part. After that or instead of that if for the `$stretcher` argument
provided the function/callable/closure then it will be used for wrapping the whole pair.

Keep in mind, that if you specify wrappers for `key` or `value` they already will
be applied before the `stretcher` callable is called!

Example bellow will help to understand the logic.

```php
$bx = bx([
	'key1' => 'val1',
	'key2' => 'val2',
	'key3' => 'val3',
])->stretched(fn($v, $k) => "(\"{$k}\": \"{$v}\")",  ' || ', '!', '?');

pd("$bx");
```

Output would be:

```text
("?key1?": "!val1!") || ("?key2?": "!val2!") || ("?key3?": "!val3!")
```
