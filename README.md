# SimpUtils

Current framework version: **0.3.1** (min required PHP: **8.0**)

Micro-framework extending PHP language with some useful perks, partly can even remind 
python 3 development capabilities.

This library (and related other libs) I develop the mostly for myself, but you 
are absolutely welcome to use it/those for your own good. 
Feel free to propose updates and creating issues, bugfixes and stuff!

At this context the words "library" and "framework" both refers to the same meaning 
of "micro-framework".

**Important:** The code is partly unfinished. If you are interested in the lib and it's 
functionality - please wait until the stable release of **1.0.0**. 
Starting from **1.0.0** version, overall architecture will remain the same (at least until 
the next major version change).

More about semantic versioning: [Semantic Versioning Explanation](https://semver.org).

----

## Index

 1. [Glossary](docs/glossary.md)
 2. [Installation](#Installation)
 3. [Ground Reasons and Design Decisions](#Ground-Reasons-and-Design-Decisions)
    1. [PHP Edges](docs/php-edges.md)
 4. [Main components overview](docs/main-components-overview.md)
 5. [Date and Time](docs/date-and-time.md)

----


## Installation

For safe and stable release, it's recommended to use the following command:
```shell
composer require spaf/simputils "~1"
```
This command will always make sure your major version is the same (because if
major version is different - then it can break expected behaviour)


The latest available version can be installed through composer (unsafe method!):
```shell
composer require spaf/simputils "*"
```


## Ground Reasons and Design Decisions

I love PHP, but some of the architectural decisions of it are "a bit" weird. 
From which I would highlight at least those (but not limited to):
 * Naming convention is not persistent even inside of the same section
   (See `Math` class)
 * Poor namespacing of the vital functionality which makes it look like a soup 
   (See `Math` class)
 * Lack of functional and comfortable basic instances like files and stuff
   (See `File` and `DateTime` (not PHP version, but library one) classes)
 * Outdated and too random ways to create "Properties" from methods of a class
   (See `Property` and `PropertyBatch` attribute classes)
 * Lack of transparent conversion between types. For example try to `echo ['my', 'array]`
   (See `Box` class)
 * Lack of easy to use DotEnv (and auto-loading) and Env Vars
   (See `File` class)
 * Lack of replaceable components
 * ETC. (Lot's of other reasons behind)


**Important stuff** about the PHP "edges", architecture and bugs: [PHP Edges](docs/php-edges.md)


Basically **SimpUtils** provides interconnected, consistent tools (more or less) 
for you to code and prototype easily.

The coolest aspect of the framework, that you can use any functionality of it, without
need of usage the whole framework code. It was developed with the logic 
of being maximally transparent and easy to use out of the box.

