### "with" love

Python specific command `with` can be easily implemented through meta-magic and callables.

Simple example:

```php

PHP::init();

class Totoro extends SimpleObject {

	protected function ___withStart($obj, $callback) {
		pr('PREPARED! %)');
//		$callback($obj);
//		return true;
	}

	protected function ___withEnd($obj) {
		pr('POST DONE %_%');
	}

}

$obj = new Totoro;

with($obj, function () {
	pr('HEY! :)');
});
```

You can access the target object easily from the callable:

```php
$obj = new Totoro;

with($obj, function ($obj) {
	pr('HEY! :)', $obj);
});

// or less elegant way:
with($obj, function () use ($obj) {
	pr('HEY! :)', $obj);
});

```

The example above can be combined if you want to use more from the outer scope,
but to keep the elegant way :)

```php
$obj = new Totoro;

$var1 = 1;
$var2 = 0.2;
$var3 = 'CooCoo';

with($obj, function ($obj) use ($var1, $var2, $var3) {
	pr('HEY! :)', $obj, $var1, $var2, $var3);
});

```

The syntax obviously is not that cute as in python, but functionally it's the same thing.

P.S. Keep in mind that the `with()` functionality relies on "MetaMagic" trait, and object
should use either the trait or implement 2 methods of `___withStart()` and `___withEnd()`
