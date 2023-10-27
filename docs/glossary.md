# Glossary of SimpUtils terms

Really important to remember, that this glossary defines and explains meaning only 
inside the framework. Please do not consider in-framework definitions in a wide
general purpose meaning.

 * [Prism](#term-prism)
 * [Property](#term-property)
 * [Virtual Property](#term-virtual-property)
 * [Real Property](#term-real-property)


<dl>
    <dt id="term-prism">Prism</dt>
    <dd>
        Special type of classes that do not provide logical meaning by themselves,
        but rather expose/modify some portion of functionality from the target class.
    </dd>
    <dd>
        A good example would be "Date" class that is being a prism for "DateTime" class.
        So when using "Date" object, it will use underlying "DateTime" target object 
        for data, but might modify some aspects of it (For example it will output 
        only date part of the "DateTime" object)
    </dd>
    <dt id="term-property">Property</dt>
    <dd>
        Any in-class variable. Including real ones and virtual ones
    </dd>
    <dd>
        Terms "Property" and "Field" can be considered as synonyms
    </dd>
    <dt id="term-virtual-property">Virtual Property</dt>
    <dd>
        In-class variables that are defined through <code>#[Property]</code> or <code>#[PropertyBatch]</code>.
    </dd>
    <dd>
        In broader sense, any in-class variables that are defined through 
        "__get" and "__set" magic methods.
    </dd>
    <dt id="term-real-property">Real Property</dt>
    <dd>
        In-class variables that are directly defined in a class (Non-virtual ones)
    </dd>
</dl>

## Additional clarifications

 1. "Lib", "Package", "Framework" and "Micro-framework" could be used in reference 
    to this code-base.
