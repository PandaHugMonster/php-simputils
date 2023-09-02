# Markers

Markers are PHP Attributes that used to "mark" code entities
(classes, methods, class-constants, properties, functions, constants, parameters).

In the most cases those markers are relevant to code quality and automatic aggregation
for code-analysis.

They do not affect the runtime of the users of the library by default. 
The idea is to keep them as non-invasive as possible, because they are needed for 
code analysis and not code execution!

## Passive and Active Markers

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

## Available Markers

**Important:** Do not cover each entity with markers, it will not make much sense.
Markers should be used responsibly, despite the fact that it will not affect the runtime!

Point of markers is to target particular code-places temporarily (in some very rare 
cases permanently).

### ~~`Affecting`~~ (deprecated)

A deprecated marker, the purpose of which suppose to highlight,
methods that might modify internal state of the object.

Was deprecated due to very narrow/limited logical meaning.
The `ObjState` marker suppose to play similar role.

`Affecting` marker will be removed starting version `2.0.0`.

### `ObjState`

The new marker that suppose to replace a previous version ~~`Affecting`~~.

It allows to mark the method with a type the object-state "influenced" with:
 * `unaffecting` - the method does not affect the object state
 * `partially-affecting` - for example not affecting the state of the whole object, but 
   do some operations with internal cache, etc.
 * `affecting` - the object state affected to some extent
 * And you are welcome to introduce your custom types, just make sure you document it well
   in your documentation. 
   Format is: `my-new-type`

**Important 1:** Do not mark "getters/setters" of virtual properties. Logically
"setter" is always `affecting` and "getter" is `partially-affecting` or `unaffecting`.

**Important 2:** Do not mark with it each method, mark only those, which meaning 
is counterintuitive or unclear from the signature/documentation
