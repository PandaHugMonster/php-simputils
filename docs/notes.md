[<< Back to README.md](../README.md)

----

# Important Notes

This section is recommended to be reviewed in case of misunderstandings.

1. [Nuances of l10n and default_tz](#Nuances-of-l10n-and-default_tz)

## Nuances of l10n and default_tz

### Nuance 1
During "library-init" process like that:

```php
use spaf\simputils\PHP;

PHP::init([
    'l10n' => 'AT',
    'default_tz' => 'Asia/Novosibirsk',
]);
```

Localization is set to "AT" (Austria), which by default sets "default_tz"
into "Europe/Vienna". But because "default_tz" is explicitly set to "Asia/Novosibirsk",
the time zone will be properly set into "Asia/Novosibirsk", and what is important
to note - that the order of those 2 arguments will not make difference after
patch version of `1.0.3`.

In short: Order of those 2 arguments will not affect the logic, in any order of those
arguments, the result in regards to them will be the same.


### Nuance 2

In case if the "default_tz" was not explicitly set, then in case of setting a new "l10n" 
will reassign the "default_tz" with the new "l10n" default_tz.

```php
use spaf\simputils\PHP;

$ic = PHP::init([
	'l10n' => 'AT',
]);

pr("{$ic->l10n->name} // {$ic->default_tz}");

$ic->l10n = 'US';
pr("{$ic->l10n->name} // {$ic->default_tz}");

// Output would be:
//      AT // Europe/Vienna
//      US // America/New_York
```

But if you explicitly set the "default_tz" at any point, the "l10n" redefinition 
will not affect "default_tz" at all.
```php
use spaf\simputils\PHP;

$ic = PHP::init([
	'l10n' => 'AT',
]);

pr("{$ic->l10n->name} // {$ic->default_tz}");

$ic->default_tz = 'America/Toronto';

$ic->l10n = 'US';
pr("{$ic->l10n->name} // {$ic->default_tz}");

// Output would be:
//      AT // Europe/Vienna
//      US // America/Toronto
```

the same result will be if you specify timezone for the "library-init"
```php
use spaf\simputils\PHP;

$ic = PHP::init([
	'l10n' => 'AT',
	'default_tz' => 'America/Toronto',
]);

pr("{$ic->l10n->name} // {$ic->default_tz}");

$ic->l10n = 'US';
pr("{$ic->l10n->name} // {$ic->default_tz}");

// Output would be:
//      US // America/Toronto
//      US // America/Toronto
```
