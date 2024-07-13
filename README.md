# PHP SimpUtils Framework

**SimpUtils** is a framework that provides tools for developers and software architects 
to improve their performance, analyze code easier and improving overall experience with PHP.

* Required PHP version: **8.0 or higher**
* License: [MIT](LICENSE)
  * Authors of this framework are not liable for any problems/damages related to usage
    of this framework. Use it on your own risk!
* The project follows [Semantic Versioning](https://semver.org)
* [Changelog](docs/changelog.md)
* [Unstable versions installation](docs/unstable-versions-installation.md)


:ukraine: Please support Ukraine in their fight against russian aggression: 
[NBU Opens Special Account to Raise Funds for Ukraine’s Armed Forces](https://bank.gov.ua/en/news/all/natsionalniy-bank-vidkriv-spetsrahunok-dlya-zboru-koshtiv-na-potrebi-armiyi)

:ukraine: #StandWithUkraine


## Installation

```shell
composer require spaf/simputils "^1"
```

## Documentation

> [!IMPORTANT]
> Always execute framework initializer `PHP::init()` as early as possible in your code:
> ```php
> use spaf\simputils\PHP;
> 
> require_once "vendor/autoload.php";
>
> PHP::init();
> ```

### Components

1. [Working with Date/Time](docs/datetime.md)
   * [Date/Time](docs/datetime.md#datetime)
   * [Date/Time atoms](docs/datetime.md#datetime-atoms)
   * [Time zones](docs/datetime.md#time-zones)
2. [Working with files]()
   * [Files and Directories]()
   * [Processing apps]()
   * [Config files]()
3. [Working with arrays]()
   * [Boxes]()
   * [Sets]()
   * [Stacks]()
4. [Data, Types and Info]()
   * [PHPInfo]() (PHP Info as an object)
   * [Versions]()
   * [Sensitive entities]() (passwords, tokens, etc.)
   * [Data types]()
   * [Calculations]()
5. [Web]()
   * [IP entities]()
   * [Urls and links]()
6. [Display and Output]()
   * [Renderers]()
7. [System]()
   * [System info]()
   * [Framework config]()
   * [Localization and Internationalization]()
8. [Refactoring, Optimization and Architecture]()
   * [Markers](docs/markers.md)
