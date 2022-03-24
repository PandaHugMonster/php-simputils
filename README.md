# SimpUtils

**SimpUtils** is a micro-framework that provides interconnected, consistent utilities 
to improve code maintenance, code quality, development speed. 
Basically it should help you to make your code more "elegant",
and provide tools for quick prototyping for any-scale project.

It extends PHP language with some useful perks. Provides similar to native classes,
but improves their capabilities. Normalizes naming and architecture.
In the most cases it should be "compatible" with any framework, and you can use it in parallel
to your main framework ( There is for now Yii2 + SimpUtils integration package: 
https://github.com/PandaHugMonster/yii2-simputils )

All the aspects of the framework were designed to improve code development and readability.
All the components and features are designed to be intuitive and transparent for common use cases,
but really flexible in case of need.

P.S. This framework (and related other libs) I develop the mostly for myself, but you
are absolutely welcome to use them for your own good.
Feel free to propose updates and creating issues, bugfixes and stuff!

----

## Index

 1. [Installation]()
 2. [Highlights]()
 3. [Glossary](docs/glossary.md)
 4. [Ground Reasons and Design Decisions](docs/reasoning-and-design.md)
 5. [Main components overview](docs/main-components-overview.md)
 6. [Date and Time](docs/date-and-time.md)

----


## Installation

Current framework version: **0.3.3**

Minimal PHP version: **8.0**

**Important:** The code is partly unfinished. If you are interested in the lib and it's
functionality - please wait until the stable release of **1.0.0**.
Starting from **1.0.0** version, overall architecture will remain the same (at least until
the next major version change).

More about semantic versioning: [Semantic Versioning Explanation](https://semver.org).

-----

For safe and stable release, it's recommended to use the following command:
```shell
composer require spaf/simputils "~1"
```
This command will always make sure your major version is the same (because if
major version is different - then it can break expected behaviour)


The latest available version can be installed through composer (**unsafe method**!):
```shell
composer require spaf/simputils "*"
```


## Highlights

__unimplemented yet__

 - [ ] Properties
 - [ ] Working with Date and Time
 - [ ] Versions
 - [ ] DotEnv
 - [ ] Easy file reading/writing
 - [ ] Data conversion
 - [ ] Advanced PHP Info
