# Starter Guide

The following few parts if this guide should give you a brief look into the capabilities 
of the framework.

## Part 1 - Basics

### Intro

Some parts of the framework are ready to be used right out of the box without any initialization.
But some parts require some initialization, for that reason it's recommended always start your app
from framework initialization like this (ideally it should be "the first thing to run" 
after composer autoload):
```php

use spaf\simputils\PHP;

// Composer autoload
require_once 'vendor/autoload.php';

// SimpUtils framework init
PHP::init();

```

**Important**: Keep in mind, in the examples further this initialization code could be omitted 
for convenience of reading, but you always have to use it when coding. 

The `PHP::init()` is the initialization method of the framework. 
It returns "IC" (init-config), so you could use it right away, but if you need it later, 
you always can pick it up through `ic()` function (IC can be obtained only after 
initialization!).

```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\ic;

$ic = PHP::init();

// OR

$ic = ic();

pr("{$ic}");

```

Output:
```text
InitConfig[name=app, code_root=/home/ivan/development/php-simputils, working_dir=/home/ivan/development/php-simputils, init_blocks=["spaf\\simputils\\components\\initblocks\\DotEnvInitBlock"]]
```

Beside that, you could provide some parameters for the initialization.
For examples you could redefine some components, change/set localization and timezone,
change some aspects like exec-environments and/or activate or deactivate additional 
initialization blocks (.env autoimport, etc.)

What you can specify for the config:
* `$init_blocks` (array) - By default initializes such as "DotEnv autoimport", etc.
* 


## Part 2 - Advanced


## Part 3 - Some Use-Cases


## Part 4 - Best Practices
