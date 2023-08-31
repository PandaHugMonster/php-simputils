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
