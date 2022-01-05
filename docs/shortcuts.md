# Shortcuts

Shortcuts representing aliases for particular functionality.

**Important:** Even though it has a name "shortcut", the real shortcuts
are not always shorter than the initial functionality invocation.

There are 2 types of shortcuts "basic shortcuts" and "shortcut aliases"

## Basic shortcuts

**Basic shortcuts** - are those that are defined inside of a namespace without class.
Usually they are defined in `basic.php` file.

**Shortcut aliases** or just a "Shortcut" - are class-level methods that are being just 
an "Alias" of some other functionality.

Any shortcuts - must not implement any logic except sub-supplying to 
the target functionality. Additionally for a better static analysis it's a good practice
to mark those methods with `#[Shortcut]` attribute.

**Important:** The framework defines only basic core shortcuts in a `basic.php`.
Other libraries will define their own basic shortcuts in their own `basic.php` files.

Here are basic core shortcuts:
1. `pd()`  - Please Die method shortcut | [pd()](#pd)
2. `box()` - returns `Box` array wrapper object | [box()](#box)
3. `now()` - returns [DateTime](about-date-time.md) object of a current
   date time | [now()](#now)
4. `ts()`  - returns [DateTime](about-date-time.md) object of specified
   date time | [ts()](#ts)
5. `fl()`  - returns `File` object representing real or
   virtual file | [fl()](#fl)
6. `env()` - if argument provided then returns value of [Env Vars](env-vars.md)
   or null, otherwise returns the full array of `$_ENV` | [env()](#env)

