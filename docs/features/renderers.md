# Renderers feature

Feature was added in version `1.1.5`.

Renderers is a PHP Attribute `\spaf\simputils\attributes\Renderer` that applied to 
static methods (usually of static helper classes).
And for functionality to work you should use a trait `\spaf\simputils\traits\StaticRendererTrait`
for your class.

The method to which you apply this attribute must return `null` or 
`\spaf\simputils\components\RenderedWrapper` object.

Then you just use `render` method of your class to render the object. If your marked method
will return `null`, then it will try another method marked with `Renderer` attribute.

The main idea of renderers is to have a method `render` called on any object (renderers must support
this object/class type), and it will be turned into correct format. Good example is HTML renderers.

**Important:** `r()` method is a shortcut of `render()`. 

The simplest usage example is DateTime HTML rendering:

```php
use spaf\simputils\Html;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pr;

$dt = now();

$res = Html::r($dt);
//$res = Html::render($dt);

pr($res);
```

Output:
```html
<time datetime="2023-09-08T02:03:36+02:00">2023-09-08 02:03:36</time>
```

Equivalent of the example above could be:

```php
use spaf\simputils\Html;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pr;

$dt = now();

$res = Html::dt($dt);

// Keep in mind, that direct method returns the same data, but wrapped into
// a stringifiable object. So for `pr()` or `pd()` don't forget to stringify it.
pr("{$res}");
```

Output (exactly the same):
```html
<time datetime="2023-09-08T02:03:36+02:00">2023-09-08 02:03:36</time>
```

It seems odd to add such complexity for a single case method, but imagine that there are 
multiple object types that should be turned into their own widgets, and for all of those cases
you just use `Html::r()` or `Html::render()` method.

## Extendability

The idea behind `Html` rendering class (or any other rendering class), 
to provide a base for you custom inherited class, and implement custom renderers,
or even attach traits with rendering methods. 

This way you can extend your key helper classes with functionality from side libraries.

```php

use spaf\simputils\Html as HtmlBase;
use spaf\simputils\Str;
use function spaf\simputils\basic\now;
use function spaf\simputils\basic\pr;

class Html extends HtmlBase {

	#[Renderer]
	static function myString($arg, ...$args): ?RenderedWrapper {
		if (Str::startsWith($arg, 'my')) {
			return new RenderedWrapper(
				static::tag('div', $arg, ['data-type' => 'test'])
			);
		}
		return null;
	}

}



// DateTime renderer will render it as HTML entity "time"
$res = Html::r(now());
pr("{$res}");

// Default renderer will be used if no specific ones are available, and will output
// the value as a string (basically no change)
$res = Html::r('simple string');
pr("{$res}");

// The string starts with "my" substring, so `myString()` renderer will be used.
$res = Html::r('my simple string');
pr("{$res}");

```

Output:
```html
<time datetime="2023-09-09T13:36:42+02:00">2023-09-09 13:36:42</time>
simple string
<div data-type="test">my simple string</div>
```

## Performance considerations

Though there shouldn't be much issues with performance, just keep in mind, that for the purpose
of choosing correct rendering method, they are executed sequentially until the non-`null`
returning method is found. So if you will implement super-huge amount of rendering methods,
this could affect performance.

If you face that issue, you always can adjust granularity of your rendering methods.
And group similar functionality within a bigger/chunkier rendering method.

If you are a library developer that wants to provide own traits with rendering methods,
it's a good idea to keep in line how many such methods are added, or maybe even better 
to implement a single rendering method per trait. 

## Unfinished functionality

This functionality more or less stable, but it's a bare minimum or POC.

It lacks some significant functionality (renderers order control over inheritance, etc.).

Just keep in mind that it's unfinished for now.
