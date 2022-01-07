[<< Back to README.md](../README.md)

----

# PHP edges, architecture and bugs

## Intro
The result of this framework is build to cover up some of the non-efficient or 
uncomfortable to use aspects of the PHP engine. And during development of this framwork
I faced even more of "weird stuff".

In total: PHP is super awesome! but can be even more awesome if fixed.


## Index

 1. [Native PHP objects bugs](#Native-PHP-objects-bugs)
 2. [No static magic methods for set and get](#No-static-magic-methods-for-set-and-get)
 3. [Nameless soup](#Nameless-soup)


----


## Native PHP objects bug

**2022-01-05** - I have found out that `print_r` or any other debugging printing/string tool
that must use array from `__debugInfo` for output, does not use this custom magic method,
when extending your class from native PHP class. For example `DateTime`.

In the recent code I was trying to resolve it, but I can't it's a PHP bug, and old one for sure,
even in PHP 8.1 still not resolved. 

So in matter of `DateTime` or any other framework class that is extended from PHP native
classes will not output well with `pr()`, `prstr()`, `pd()`, `print_r()` and others.

**All original classes not extended from PHP natives - are working very well!**
You can check out how outputs this command:

```php

use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\pr;

pr(fl());


```

Output would be something like that:
```php
spaf\simputils\models\File Object
(
    [stat] => spaf\simputils\models\Box Object
        (
            [0] => 12
            [1] => 0
            [2] => 33206
            [3] => 1
            [4] => 0
            [5] => 0
            [6] => -1
            [7] => 0
            [8] => 0
            [9] => 0
            [10] => 0
            [11] => -1
            [12] => -1
            [dev] => 12
            [ino] => 0
            [mode] => 33206
            [nlink] => 1
            [uid] => 0
            [gid] => 0
            [rdev] => -1
            [size] => 0
            [atime] => 0
            [mtime] => 0
            [ctime] => 0
            [blksize] => -1
            [blocks] => -1
        )

    [size] => 0
    [app] => spaf\simputils\models\files\apps\TextProcessor Object
        (
            [obj_id] => 116
            [obj_type] => spaf\simputils\models\files\apps\TextProcessor
        )

    [content] => ****
    [exists] => 
    [backup_location] => 
    [backup_content] => ****
    [fd] => Resource id #49
    [uri] => urn:
    [mime_type] => 
    [md5] => 
    [size_hr] => 0B
    [extension] => 
    [name] => 
    [name_full] => 
    [path] => 
    [is_local] => 1
    [urn] => urn:
    [obj_id] => 117
    [obj_type] => spaf\simputils\models\File
    [is_backup_preserved] => 
    [_is_default_app] => 1
    [_backup_file] => 
    [processor_settings] => 
)

```

## No static magic methods for set and get

For eternity of times that functionality was "ignored" since 2006 - 2008 years, when it was
reported multiple times.

Despite all the marvelous improvements for 8.0 and 8.1. That functionality does not exist "yet".
So it's impossible to implement static "getter" and "setter" :(. I tried to find hacks.
I could not find any that would work in a native way.


Here it is one of many tickets on a bug tracker: https://bugs.php.net/bug.php?id=45002

The last comment stated:

>[2021-08-10 16:36 UTC] cmb@php.net
>> Even if this feature request had a billion upvotes and as much
>> comments, it won't be implemented, unless someone cared to go
>> through the RFC process[1].  For the time being I'm suspending
>> this ticket.
>>
>> https://wiki.php.net/rfc/howto


## Nameless soup
The most disappointing in PHP is super inconsistent naming,
and lack of Namespaced functionality.

This framework resolves those problems.
Step-by-step I will normalize all the common functionality. For example you can take 
a look into core static `Math` class.
PHP native "Math" info: https://www.php.net/manual/en/ref.math.php

Another problem is naming. For example native php function 
[\rad2deg()](https://www.php.net/manual/en/function.rad2deg.php) which basically
is a "converter function" from one format to another has "2" in the name, 
while [\bindec()](https://www.php.net/manual/en/function.bindec.php) is a converter as well, 
but has no "2" in the name.

What makes it even worse [\hex2bin()](https://www.php.net/manual/en/function.hex2bin.php)
is in "String Functions" section (maybe reasonably!), but again having "2" in the name.
