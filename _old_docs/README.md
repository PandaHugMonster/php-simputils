:ukraine: #StandWithUkraine

This is the official Bank of Ukraine link for donations for Ukraine:

https://bank.gov.ua/en/news/all/natsionalniy-bank-vidkriv-spetsrahunok-dlya-zboru-koshtiv-na-potrebi-armiyi

-----

-----

# SimpUtils

## Description

**SimpUtils** is a micro-framework that provides really simple and lightweight tools
for development and prototyping. Additionally, there are tools for comfortable and efficient
optimization and debugging.

The framework does not have much of composer dependencies (on purpose), to make sure that
it does not bloat your `vendor` folder.

The main purpose of the framework is to improve development experience, and it does not oppose
any of the popular framework giants like "Yii2/3", "Laravel" or "Zend". The **SimpUtils**
can be easily used in combination of any framework.


The framework extends PHP language with some useful perks. Provides similar to native classes,
but improves their capabilities. Normalizes naming and architecture.

All the aspects of the framework were designed to improve code development and readability.
All the components and features are designed to be intuitive and transparent for common use cases,
but really flexible in case of need (Version `1.*.*` has some architectural flaws though,
those will be eliminated from version `2.0.0`).

P.S. The framework I develop for my own projects, but I'm really happy to share it with
anyone who is interested in it. Feel free to participate! Suggestions, bug-reports.
I will be really happy hearing from you.



## Info

 * Minimal PHP version: **8.0**
 * Current framework version: **1.1.6**
 * License: [MIT](LICENSE)
   * Authors of this framework are not liable for any problems/damages related to usage 
     of this framework. Use it on your own risk!
 * Examples and Usage:
   1. [Quick Start](#Quick-Start)
      1. [Installation](#installation)
      2. [Minimal usage](#Minimal-usage)
   2. Features:
      1. [Markers](docs/features/markers.md)
      2. [Renderers](docs/features/renderers.md)
      3. [Working with URLs](docs/features/urls.md)
      4. [Files, Data Files and DotEnv](docs/features/files.md)
      5. [Properties](docs/features/properties.md)
      6. [Date Times](docs/features/date-times.md)
      7. [Advanced PHP Info Object](docs/features/phpinfo.md)
      8. [IPv4 model](docs/features/ip-models.md)
      9. [Path-alike Box-array](docs/features/boxes.md#Path-alike-Box-array)
      10. [Stretchable feature of Box-array](docs/features/boxes.md#Stretchable-feature-of-Box-array) (`paramsAlike()`)
      11. ["with" love](docs/features/with-love.md)
      12. [Passwords and Secrets explained](docs/passwords-and-secrets.md)
   3. [Additional Scripts](docs/additional-scripts.md)
   4. [Changelog](docs/changelog.md)
   4. [Glossary](docs/glossary.md)
   5. [Structure](docs/structure.md)
   6. [Important notes](docs/notes.md) - this can help with troubleshooting

-----

## Quick Start

### Installation

Minimal PHP version: **8.0**

```shell
composer require spaf/simputils "^1"
```

Keep in mind that the library development suppose to follow the semantic versioning,
so the functionality within the same major version - should be backward-compatible (Except
cases of bugs and some rare issues).

More about semantic versioning: [Semantic Versioning](https://semver.org).

Unstable: [Unstable Versions Installation](docs/unstable-installation.md)

### Minimal usage

Despite the fact that it's suggested to run `PHP::init()` method before your code base,
you can use some features out of the box even without doing so.

It's just recommended to initialize framework before usage (some significant portion
of the functionality might rely on the initialization process).

```php

use spaf\simputils\PHP;

require_once 'vendor/autoload.php';

PHP::init();

// Here goes your code

```

It's very important to make sure that you include the composer `vendor/autoload.php` file
before usage of the framework.

-----

----

Really important to note - a lot of functionality is still not documented.
At some point documentation will be improved to cover all the functionality and examples.
