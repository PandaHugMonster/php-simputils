# PHP static class

One of the key features of the framework is class `\spaf\simputils\PHP`.
This class represents the normalization (as I see it) of the common functionality that is needed for
a developer to work.

In some cases it could be simplified shortcuts to some of the standard PHP functionality,
in some cases it could be improved functionality of the similar standard PHP functionality.

For example `PHP::serialize()` and `PHP::deserialize()` are similar to standard PHP `\serialize()`
and `\unserialize()`, but having flexibility to choose mechanism to use, which by default is using
JSON format, and not the special serialization notation of PHP (again, if you want the same 
functionality that standard PHP provides, you can switch `PHP::serialize()` and `PHP::deserialize()`
to use the standard PHP functionality instead).

**Important note:** This class (and the whole framework) suppose to fix and improve usage of PHP, 
through more comfortable implementation of common functionality, for example like strings "yes", 
"true", "t", "1", etc. converting on the fly to the boolean value `true`. The mostly because
during my applications development process I don't want to think over such simple utility stuff,
I would rather dedicate additional time **for the architecture of my application/solution**
using already commonly used toolkit.

## PHP::pd() - Please die

This functionality in the most cases should be used directly from 
a shortcut: `\spaf\simputils\basic\pd()`.

More information about it here: [In-place quick debugging or PleaseDie](use-cases-debugging.md)

## PHP::asBool() - Converts anything to bool

