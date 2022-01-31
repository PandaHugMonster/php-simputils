[<< Back to README.md](../README.md)

----

# Main components overview

1. [Core Shortcuts]()
2. [Core Static Classes and Functions]()
3. [Core Models]()
4. [Core Attributes]()

## Core Shortcuts

More info about shortcuts here: [Shortcuts](shortcuts.md)
1. `pd()`  - Please Die method shortcut | [pd()](shortcuts.md#pd)
2. `box()` - returns `Box` array wrapper object | [box()](shortcuts.md#box)
3. `now()` - returns [DateTime](date-and-time.md#DateTime-model) object of a current
   date time | [now()](shortcuts.md#now)
4. `ts()`  - returns [DateTime](date-and-time.md#DateTime-model) object of specified
   date time | [ts()](shortcuts.md#ts)
5. `fl()`  - returns `File` object representing real or
   virtual file | [fl()](shortcuts.md#fl)
6. `env()` - if argument provided then returns value of [Env Vars](env-vars.md)
   or null, otherwise returns the full array of `$_ENV` | [env()](shortcuts.md#env)


## Core Static Classes and Functions

1. `\spaf\simputils\PHP` main static class provides some key php-wise functionality
   and quick methods.
2. `\spaf\simputils\Math` static class of **math functionality**. The mostly
   contains shortcuts of the php-native functions for math.
3. `\spaf\simputils\Str` static class of **strings-related functionality**.
4. `\spaf\simputils\Boolean` static class of **bool-related functionality**.
5. `\spaf\simputils\FS` static class of **file-related functionality**.
6. `\spaf\simputils\Data` static class to **convert data units** (bytes to kb, etc.).
7. `\spaf\simputils\DT` static class providing functionality for **date and time**.
8. `\spaf\simputils\System` static class providing access to **platform/system info**.
9. `\spaf\simputils\basic` set of namespaced functions, **commonly used ones**.


## Core Models

1. `\spaf\simputils\models\Box` - model class as a wrapper for primitive arrays
2. `\spaf\simputils\models\DateTime` - model for datetime
   value [DateTime model](date-and-time.md#DateTime-model)
3. `\spaf\simputils\models\File` - model for file value
4. `\spaf\simputils\models\GitRepo` - model representing minimal git functionality
   (through shell commands)
5. `\spaf\simputils\models\InitConfig` - Config for initialization process (bootstrapping,
   components redefinition and other stuff)
6. `\spaf\simputils\models\PhpInfo` - really advanced version of `phpinfo()` in form of
   iterable object. Contains almost all of the relevant data from `phpinfo()`
   but in parsed and extended state (for examples version info is wrapped into `Version`
   objects). May be extended even further, so will provide much more benefits, than
   clumsy native `phpinfo()`
7. `\spaf\simputils\models\Version` - represents (and parses/generate) version value
8. `\spaf\simputils\models\SystemFingerprint` - represents fingerprint of the system/data


## Core Attributes

1. `\spaf\simputils\attributes\Property` used for marking methods to behave like
   Properties
2. `\spaf\simputils\attributes\PropertyBatch` similar to `Property`, but allows
   to specify Properties in a batch mode
3. `\spaf\simputils\attributes\markers\Shortcut` marking attribute to indicate method
   or function as a "Shortcut" to another functionality/variable
4. `\spaf\simputils\attributes\markers\Deprecated` marking attribute to indicate anything
   as a deprecated element
5. `\spaf\simputils\attributes\markers\Affecting` - should not be used. Unfinished concept

**Really quick reasoning:** You might ask why do we need `Deprecated` attribute, when we
have JetBrains' (PHPStorm) composer dependency for similar attributes.
And the answer would be: I really adore and love JetBrains and all of their products,
but I can not let having additional composer dependency just for a few attributes.
