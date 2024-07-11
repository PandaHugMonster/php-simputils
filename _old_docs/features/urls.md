# Working with URLs

The new feature of "URL" object (`UrlObject`) has arrived and almost finished.
After recent update it's tested enough to be considered stable (even though some unconventional
cases of bugs can still occur).

Example:

```php
use function spaf\simputils\basic\url;

PHP::init();

$url = url('localhost', ['booo', 'fooo'], ['godzila' => 'tamdam', '#' => 'jjj'], port: 8080);

pr($url, "{$url}");
```

Output would be:

```text
spaf\simputils\models\UrlObject Object
(
    [for_system] => ****
    [for_user] => https://localhost:8080/booo/fooo?godzila=tamdam#jjj
    [host] => localhost
    [orig] => ****
    [params] => spaf\simputils\models\Box Object
        (
            [godzila] => tamdam
        )

    [password] => ****
    [path] => spaf\simputils\models\Box Object
        (
            [0] => booo
            [1] => fooo
        )

    [port] => 8080
    [processor] => spaf\simputils\models\urls\processors\HttpProtocolProcessor
    [protocol] => https
    [relative] => /booo/fooo?godzila=tamdam#jjj
    [sharpy] => jjj
    [user] => 
)

https://localhost:8080/booo/fooo?godzila=tamdam#jjj
```

It can not only generate, but parse as well and even combine parts:

```php

use function spaf\simputils\basic\url;
PHP::init();

$url = url(
	// It will first parse this, and extract all the relevant stuff
	'http://my.spec.domain.com.ru.at/path1/path2?param1=val1&param2=val2',

	// Then it will use path and add it to the existing path
	['path_3', 'path_4'],

	// And after that adds the params to existing ones (or overrides them by key)
	[
		'param_30' => 'val-30',
		'param_40' => 'val-40',
		// Sharpy can be defined/redefined like this
		'#' => 'new-sharpy!'
	]
);

pr($url, "{$url}");
```

Output would be:

```text

spaf\simputils\models\UrlObject Object
(
    [for_system] => ****
    [for_user] => http://my.spec.domain.com.ru.at/path1/path2/path_3/path_4?param1=val1&param2=val2&param_30=val-30&param_40=val-40#new-sharpy!
    [host] => my.spec.domain.com.ru.at
    [orig] => ****
    [params] => spaf\simputils\models\Box Object
        (
            [param1] => val1
            [param2] => val2
            [param_30] => val-30
            [param_40] => val-40
        )

    [password] => ****
    [path] => spaf\simputils\models\Box Object
        (
            [0] => path1
            [1] => path2
            [2] => path_3
            [3] => path_4
        )

    [port] => 80
    [processor] => spaf\simputils\models\urls\processors\HttpProtocolProcessor
    [protocol] => http
    [relative] => /path1/path2/path_3/path_4?param1=val1&param2=val2&param_30=val-30&param_40=val-40#new-sharpy!
    [sharpy] => new-sharpy!
    [user] => 
)

http://my.spec.domain.com.ru.at/path1/path2/path_3/path_4?param1=val1&param2=val2&param_30=val-30&param_40=val-40#new-sharpy!

```

And after all that you could get parts separately and play around with them.

For example, we get "path" parts, and they are returned as a Box-array, you can
work with them sequentially, but as soon as you stringify them, they turn back to "path" string
again:

```php

use function spaf\simputils\basic\url;
PHP::init();

$url = url(
	// It will first parse this, and extract all the relevant stuff
	'http://my.spec.domain.com.ru.at/path1/path2?param1=val1&param2=val2',

	// Then it will use path and add it to the existing path
	['path_3', 'path_4'],

	// And after that adds the params to existing ones (or overrides them by key)
	[
		'param_30' => 'val-30',
		'param_40' => 'val-40',
		// Sharpy can be defined/redefined like this
		'#' => 'new-sharpy!'
	]
);

$path = $url->path;
$path->append('HUGE-PATH-ADDITION');
$path[1] = 'I_REPLACED_PATH2_PIECE';
$stringified_path = "My path really is: {$path}";

pr($stringified_path);

```

The output would be:

```text
My path really is: path1/I_REPLACED_PATH2_PIECE/path_3/path_4/HUGE-PATH-ADDITION
```

Another moment worth mentioning, that when you modify the "path" box object -
it will affect the url object as well. (if you want to avoid that,
always clone the object instead)

The same example as above, but outputting the whole url object now:

```php

use function spaf\simputils\basic\url;
PHP::init();

$url = url(
	// It will first parse this, and extract all the relevant stuff
	'http://my.spec.domain.com.ru.at/path1/path2?param1=val1&param2=val2',

	// Then it will use path and add it to the existing path
	['path_3', 'path_4'],

	// And after that adds the params to existing ones (or overrides them by key)
	[
		'param_30' => 'val-30',
		'param_40' => 'val-40',
		// Sharpy can be defined/redefined like this
		'#' => 'new-sharpy!'
	]
);

// Important, here is the assigning by reference, not cloning!
$path = $url->path;

$path->append('HUGE-PATH-ADDITION');
$path[1] = 'I_REPLACED_PATH2_PIECE';

pr($url, "{$url}");
```

Output would be:

```text
spaf\simputils\models\UrlObject Object
(
    [for_system] => ****
    [for_user] => http://my.spec.domain.com.ru.at/path1/I_REPLACED_PATH2_PIECE/path_3/path_4/HUGE-PATH-ADDITION?param1=val1&param2=val2&param_30=val-30&param_40=val-40#new-sharpy!
    [host] => my.spec.domain.com.ru.at
    [orig] => ****
    [params] => spaf\simputils\models\Box Object
        (
            [param1] => val1
            [param2] => val2
            [param_30] => val-30
            [param_40] => val-40
        )

    [password] => ****
    [path] => spaf\simputils\models\Box Object
        (
            [0] => path1
            [1] => I_REPLACED_PATH2_PIECE
            [2] => path_3
            [3] => path_4
            [4] => HUGE-PATH-ADDITION
        )

    [port] => 80
    [processor] => spaf\simputils\models\urls\processors\HttpProtocolProcessor
    [protocol] => http
    [relative] => /path1/I_REPLACED_PATH2_PIECE/path_3/path_4/HUGE-PATH-ADDITION?param1=val1&param2=val2&param_30=val-30&param_40=val-40#new-sharpy!
    [sharpy] => new-sharpy!
    [user] => 
)

http://my.spec.domain.com.ru.at/path1/I_REPLACED_PATH2_PIECE/path_3/path_4/HUGE-PATH-ADDITION?param1=val1&param2=val2&param_30=val-30&param_40=val-40#new-sharpy!

```

All of the above work similar to params.

**Important:** For string arguments, full parsing happening only for `$host` parameter,
additional path (+params+sharpy) parsing happening for `$path` (uri) parameter,
and `$params` does not do the "string-parsing". String is not allowed as data type for `$params`
argument (at least for now).

## Current page / Active Url

New method prepared for getting the current Url (works only for web, will not work for CLI
without faking some params):

```php

PHP::init();

$url = PHP::currentUrl();

pd($url);

```

Output might depend on your web-server:

```text

spaf\simputils\models\UrlObject Object
(
    [for_system] => ****
    [for_user] => https://localhost:8080/booo/fooo?godzila=tamdam#jjj
    [host] => localhost
    [orig] => ****
    [params] => spaf\simputils\models\Box Object
        (
            [godzila] => tamdam
        )

    [password] => ****
    [path] => spaf\simputils\models\Box Object
        (
            [0] => booo
            [1] => fooo
        )

    [port] => 8080
    [processor] => spaf\simputils\models\urls\processors\HttpProtocolProcessor
    [protocol] => https
    [relative] => /booo/fooo?godzila=tamdam#jjj
    [sharpy] => jjj
    [user] => 
)

```

This allows to identify if the `$url` object is Current Url or Active Url:

```php

$current = PHP::currentUrl();
$url = url(port: 9090);

pr("current: {$current}");
pr("url: {$url}");
pr("the same: ".Boolean::to($url->isCurrent()));

```

The output might depend on your web-server:

```text

current: https://localhost:8080/booo/fooo?godzila=tamdam#jjj
url: https://localhost:9090/
the same: false

```

There are nice parameters of `UrlObject::isCurrent()` that could be tweaked.
Besides that there is another comparison method for 2 different urls, and not
only the current web-page url `UrlObject::isSimilar()`, structure of which is almost
the same.

[//]: # (FIX    !!!!!)
SOME FEATURES ARE NOT FULLY IMPLEMENTED

* url extension like a "russian-nesting doll"
* "params" parameter parsing strings
* Support for other protocols except HTTP(S)
* maybe something else as well!
