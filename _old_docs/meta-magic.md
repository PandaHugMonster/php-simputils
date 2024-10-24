# Meta-Magic

Meta-Magic is a fusion of "PHP Magic Methods" and PHP 8.0+ feature of PHP Attributes.

It is a core functionality of the framework.

All the SimpUtils PHP Attributes are being part of Meta-Magic concept (before version `2.0.0`
not everything is fully transitioned, but the concept is valid nevertheless).

So any PHP Attribute of SimpUtils is a **Meta-Magic**.

**Important:** Not everything yet implemented as described on this page. 
But this page is the latest and the most up-to-date reference.

## Reasoning

The major purpose for Meta-Magic:
* Integration of PHP Attributes into a runtime
  * (**Not yet implemented**) Advanced filtering and search of signatures with 
    a specified PHP Attributes.
* (**Not yet implemented**) Improve usage of PHP Magic Methods with help of 
  special PHP Attributes of SimpUtils
  * Use of those Magic-Methods is discouraged
  * Instead of two underscore methods like `__clone()` the PHP Attributes
    could be used like `#[Clone]`, etc.
* (**Not yet implemented**) Proper and comfortable casting of objects from/to other types
  * `#[CastFrom()]`
  * `#[CastTo()]` + basic shortcuts like `int()`, `str()`, `bool()`, `cast()`, etc.
* 
