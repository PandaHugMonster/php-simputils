[<< Back to README.md](../README.md)

----

# Ground Reasons and Design Decisions

**Important stuff** about the PHP "edges", architecture and bugs: [PHP Edges](php-edges.md)

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
