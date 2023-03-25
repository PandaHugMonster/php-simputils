# Custom Debug-Output Functionality
## Description and Rationale

"CDOF" or Custom Debug-Output Functionality - is a tool to improve or change 
the debug output of such functions like `pd()`, `pr()` or `prstr()`.
It can even improve parsibility and testing/mocking capabilities for developers.

P.S. Both `\var_dump()` and `\print_r()` will be covered in this section

### Description of issues

#### Using `\print_r()`

Previously the function `\print_r()` has been used to display data/objects for debugging 
purposes. Where output could be something like this:
```php
not null str

not null str

normal string
12.2
12
Array
(
    [simple] => array
    [ggg] => 2
    [another-array] => Array
        (
            [0] => this
            [1] => is
            [2] => sub
            [3] => array
        )

)

spaf\simputils\models\Box Object
(
    [box] => simple
    [jjj] => 1
)
```

There are a few **general problems** of such output format:
 * `null` value is output like empty string/value (as well as empty strings and `false`)
 * **Strings** are output without quote/double-quote, what makes it difficult to relate to
   string-type sometimes, especially if you have output of some other instances like "objects".
   (take a look on `[simple] => array` where `array` is a string, and not an actual array!)
 * **Boolean** are output very weirdly when `true` is output like integer `1`, but
   `false` is output like `` (empty string), in combination with empty strings and/or
   `null` values, it's impossible to understand just by reviewing the output.
 * **Arrays** are displayed in a very "roomy" way with lot's of space lost for extra
   new lines (`Array` word on one line, then opening bracket on the separate line,
   and after the internal `Array` there is an additional empty line before closing 
   last bracket)
 * **Objects** which are for the output should be very similar to array outputs taking
   an additional line for the type specification (when type specification is important,
   but losing the whole line when the next one contains just a single bracket is unwise)
 * Does not output info about object id, which is really relevant
 * Poor handling of infinite recursion in objects/arrays (`*RECURSION*` used when 
   infinite recursion is found)

And there are **deeper issues** of such output format:
 * Impossible to customize output-format (to json, or native PHP-compatible, html or 
   cli highlights, etc.)
 * Issues with inherited native PHP objects like `\DateTime`. 
   This issue prevents the output using data from `__debugInfo()` magic method.
 * There is no way to control the output details (show/hide data-types, etc.)
 * No possibility for code-highlight in `.md` documents (github, gitlab, etc.),
   or plain text display, or "php" but the highlight then is more random, than useful!

#### Using `\var_dump()`

Besides `\print_r()` the function `\var_dump()` has been used to display data/objects 
for debugging purposes. Where output could be something like this:
```php
string(12) "not null str"

string(0) ""

string(12) "not null str"

NULL

string(13) "normal string"

float(12.2)

float(12)

array(3) {
  ["simple"]=>
  string(5) "array"
  ["ggg"]=>
  int(2)
  ["another-array"]=>
  array(4) {
    [0]=>
    string(4) "this"
    [1]=>
    string(2) "is"
    [2]=>
    string(3) "sub"
    [3]=>
    string(5) "array"
  }
}

object(spaf\simputils\models\Box)#112 (2) {
  ["box"]=>
  string(6) "simple"
  ["jjj"]=>
  bool(true)
}
```

There are a few **general problems** of such output format:
* Some parts of the format is slightly better than in `\print_r()`, but the most of it
  is difficult to read/process.
* Each value has a separate line in complex objects/arrays
* Weird indentation
* A lot of additional marks/symbols that overcomplicate the output (for example length, etc)
* Inconsistency in the type + length. For example complex objects/arrays having length
  for amount of fields, when primitives output as **values** in the same way.
* Each separate value to display has an additional `\n` which is not that bad, but causing
  too long output.
* Poor handling of infinite recursion in objects/arrays (`*RECURSION*` used when 
  infinite recursion is found)

And there are **deeper issues** of such output format (basically the same as with 
previous function):
* Impossible to customize output-format (to json, or native PHP-compatible, html or
  cli highlights, etc.)
* Issues with inherited native PHP objects like `\DateTime`.
  This issue prevents the output using data from `__debugInfo()` magic method.
* There is no way to control the output details (show/hide data-types, etc.)
* No possibility for code-highlight in `.md` documents (github, gitlab, etc.),
  or plain text display, or "php" but the highlight then is more random, than useful!


### Requirements for the new way
To resolve all previously mentioned issues the new functionality should be implemented,
applying the following requirements:
1. Debug Output must be customizable
2. Default format must be compatible with default PHP syntax (should be easily 
   copied to PHP and successfully executed almost without changes)
    * Must be compatible with `.md` highlights
    * Should be easily usable for testing/mocking purposes
3. Instead of detecting Infinite Recursion, reference to already output instance 
   must be represented
   * Non-object references cannot be detected at this point.
4. Enable/disable type notation in output
5. Even with disabled explicit type notation, the clear understanding of type
   must be easily inferred from the value (quotes for strings, null, etc.)
6. Non-existing and non initialized variables/fields should be displayed as null.
   * type for null will be always omitted
7. 


### Proposal

