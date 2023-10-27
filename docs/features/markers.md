# Markers

Markers are PHP Attributes that being used to "mark" code entities
(classes, methods, class-constants, properties, functions, constants, parameters).

In the most cases those markers are relevant to code quality and automatic aggregation
for code-analysis.

They do not affect the runtime of users of the library by default. 
The idea is to keep them as non-invasive as possible, because they are needed for 
code analysis and not code execution!

Keep in mind that __all the Markers are PHP Attributes__ but **not all the PHP Attributes
are Markers**!

## Types of Markers (type of marker)

<dl>
    <dt id="marker-passive">Passive Marker</dt>
    <dd>
        When aggregated <font color="green">will not cause</font> CLI exit codes other 
		than <code>0</code>. So basically are irrelevant for CI/CD, and the most 
		relevant to code quality reports.
    </dd>
    <dt id="marker-active">Active Marker</dt>
    <dd>
        When aggregated <font color="#ff4500">might cause</font> CLI exit codes other than <code>0</code>.
		Relevant for CI/CD and code quality reports.
    </dd>
</dl>

## Hybrid Markers (nature of marker)

Hybrid markers are not markers PHP Attributes, but real **code-affecting** PHP Attributes,
like `ComaptibilityReference` that affect code execution/runtime, but are subjects for
analysis as markers for CI/CD pipelines, etc.

<dl>
    <dt id="marker-active">Hybrid Marker</dt>
    <dd>
        It is a non-marker PHP Attribute that affects runtime, but could be analysed by the side
        tools as "A Marker".
    </dd>
</dl>

## Available Markers

**Important:** Do not cover each entity with markers, it will not make much sense.
Markers should be used responsibly, despite the fact that it will not affect the runtime!

Point of markers is to target particular code-places temporarily (in some very rare 
cases permanently).

When "marking" something, it's highly advised to add at least a couple of `tags` to that
marker (and keep in mind "tags" consistency across your code base).
This should help filtering out Markers for analytical tools.

Very important difference between `Refactor` and `Optimize` is that 
`Refactor` means that the internal logic (internal architecture even) must get changed, 
while `Optimize` might mean just improvements of internal logic without architectural
changes. Keep in mind that __all the `Optimize` are `Refactor`__ but **not all the `Refactor`
are `Optimize`**!

There are few different groups of markers that having slightly different meaning,
and this could be used to improve clarity of the code.

* "Informational" - They are basically informational markers
   * `Shortcut`
   * `Deprecated`
   * `ObjState`
   * ~~`Affecting`~~ - deprecated
* "Issues" group - Analytical markers about existence of issues with code (they are
  usually inherited from `Issue` marker)
   * `Issue`
   * `Duplicated`
* "Refactoring" group - analytical markers as well, but containing next step with more details
  about what to do or fix (their names usually in imperative language,
  and they are usually inherited from `Refactor` marker)
  * `Refactor`
  * `Optimize`

So the "Issues" group could be used as a first analytical step, or ongoing
analysis when coders facing issues during their duties.

While the "Refactoring" group could be used in addition (suggested), instead or standalone
as a next step of analysis.

So if you know what to do with an issue use "Refactoring" markings, and if you've just
noticed an issue, but don't have time to investigate and analyse, just use "Issues" markings,
so devs in the future could work on top of those and either fix or provide "Refactoring" markings.


### ~~`Affecting`~~ (deprecated)

This marker is **passive**.

A deprecated marker, the purpose of which suppose to highlight,
methods that might modify internal state of the object.

Was deprecated due to very narrow/limited logical meaning.
The `ObjState` marker suppose to play similar role.

`Affecting` marker will be removed starting at version `2.0.0`.

### `ObjState`

This marker is **passive**.

The new marker that suppose to replace a previous version ~~`Affecting`~~.

It allows to mark a method (and only methods!) with a type the object-state "influenced" with:
 * `unaffecting` - the method does not affect the object state at all
 * `partially-affecting` - for example not affecting the state of the whole object, but 
   do some operations with internal cache, etc.
 * `affecting` - the object state affected to some extent
 * And you are welcome to introduce your custom types, just make sure you document it well
   in your documentation. 
   Format is: `my-new-type`

**Important 1:** Do not mark "getters/setters" of virtual properties. Logically
"setter" is always `affecting` and "getter" is `partially-affecting` or `unaffecting`.

**Important 2:** Do not mark with it each method, mark only those, which meaning 
is counterintuitive or unclear from the signature/documentation point of view.


### `Deprecated`

This marker is **active**.

Marking entity as "Deprecated" (End-Of-Life).

Suggested to always specify `reason` and `replacement` arguments.
Additionally suggested to specify argument `since` (version), and
`removed` (version).

Comment annotation `@deprecated` is for IDE convenience, it is not required, 
but suggested if your IDE does not support SimpUtils integration.

Example:

```php
use spaf\simputils\attributes\markers\Deprecated;
use spaf\simputils\generic\SimpleObject;

/**
 * @deprecated 
 */
#[Deprecated(
    reason: 'I do not like this class anymore',
    replacement: 'irreplaceable',
    since: '0.0.1',
    removed: '1.0.0'
)]
class MyOwnClass extends SimpleObject {

}
```

**Important:** Keep in mind that `removed` version must always be "major" version,
and the rest of it ("minor" and "patch") must be `0`.

### `Optimize`

This marker is **passive** but with "strict" mode it can behave as an **active** one.

Marking an entity that requires code-wise optimization.

```php

use spaf\simputils\attributes\markers\Optimize;
use spaf\simputils\generic\SimpleObject;

#[Optimize(
    comment: 'This class must be revised and optimized completely, it seems very odd!',
    severity: Optimize::SEVERITY_LOW
)]
class MyOwnClass extends SimpleObject {

}

```

### `Refactor`

This marker is **passive** but with "strict" mode it can behave as an **active** one.

Marking an entity that requires code-wise refactoring.

```php

use spaf\simputils\attributes\markers\Refactor;
use spaf\simputils\generic\SimpleObject;

#[Refactor(
    comment: 'This class must be refactored completely, it is terrible!',
    severity: Refactor::SEVERITY_HIGH
)]
class MyOwnClass extends SimpleObject {

}

```

### `Duplicated`

This marker is **passive** but with "strict" mode it can behave as an **active** one.

Marking entity as a duplicate of another part of the code.
It's not necessary related to an "Identical" duplicate, but logical one.
Methods that do the same, or classes that do the same or similar, and could be merged
together eventually, or refactored into a new entity combining functionality of all
duplicating or semi-duplicating parts.

Cross-markings is a good thing, but not fully necessary (though suggested and encouraged).

Example:

```php
use spaf\simputils\attributes\markers\Duplicated;
use spaf\simputils\generic\SimpleObject;

#[Duplicated(
    comment: 'This is a duplicate',
    related: [
        '\namespace1\namespace2\AnotherClass::method1',
        '\namespace1\namespace2\NewClass',
    ],
)]
class MyOwnClass extends SimpleObject {

}
```


### `Issue`

This marker is **passive** but with "strict" mode it can behave as an **active** one.

Marking an entity with any type of issue (except the ones that implemented
as separate markers).

`type` argument can be anything you want, there are predefined constants,
but you can define your own constants/values for `Issue` type.

Example:

```php
use spaf\simputils\attributes\markers\Issue;
use spaf\simputils\generic\SimpleObject;

#[Issue(
    type: Issue::TYPE_MESSY,
    comment: 'Something is very messy about this class',
    severity: Issue::SEVERITY_HIGH,
)]
class MyOwnClass extends SimpleObject {

}
```

### `Shortcut`

This marker is **passive**.

Shortcut markings are just marks of methods that play role of "shortcuts" for other
methods. They are applicable solely for methods and functions.
