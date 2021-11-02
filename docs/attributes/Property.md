## Intro
In the framework some definitions might be defined differently that commonly used:
 * `field` term is used for defined in class variables (commonly known as properties). 
   But, excluding those that defined through `Property` or `PropertyBatch` or with help 
   of `__get()` and `__set()` (so non-runtime defined "variables").
 * `property` term is used for all the `fields` and defined through `Property` or 
   `PropertyBatch` or with help of `__get()` and `__set()` (so runtime and non-runtime 
   defined "variables").
 * `dynamic property` - is usually in refer to `properties` that having method in behind
   (Keep in mind that it has nothing to do with PHP "static" functionality in classes,
   and is not being opposite to it. It's just completely unrelated name)

## Common info
1. It's a commonly good practice to consider all the `Property` and `PropertyBatch` as having own
   initialization layer, and to do not use calls to `Property` and `PropertyBatch` fields inside
   of the `Property` and `PropertyBatch` marked methods. Though, there is no limitation
   to use properties anywhere **except `PropertyBatch` marked methods**.
2. Consider always using `protected` modifier for property methods. In some cases due to nature 
   of modifiers in PHP, inheritance and the current architecture, using `private` can cause errors.


### Property

To use properties, there are generally 2 ways:


**Way 1**: The simplest (through default spaf SimpleObject)
 1. Your class should be extended from `\spaf\simputils\generic\SimpleObject` or
    using `\spaf\simputils\traits\SimpleObjectTrait` both provides opportunity
    to use Properties.
 2. Apply Property attribute on your method

----

**Way 2**: The most flexible (to use solely Properties functionality):
 1. Use `\spaf\simputils\traits\PropertiesTrait` on your generic/basic object(s) from
    which you extend other objects. You can apply trait directly on your target objects,
    but it seems not really efficient way of usage.
 2. Apply Property attribute on your method

----

Example of Property:

```php
    use spaf\simputils\attributes\Property;
    
    class Z1 {
    
        #[Property]
        public function getMyMethodName() {
            return 'Value of property';
        }
        
    }
```

A few conditions to bear in mind while using properties:
 1. **1 single method** or **2 separate methods** can be used to implement property
 2. `Property` attribute can be applied only on methods (class functions)
    `PropertyBatch` attribute can be applied on a class non-static fields and methods.
 3. In case of redefinition of class's magical methods `__get`, `__set` and `__isset`
    the Property trait's "parent" methods must be invoked.
    * This is why it's advised against of direct trait usage, but to use a layer class
      before all of your children-classes. This is due to nature of traits in PHP.
      If you will override those methods directly on the class you use the trait,
      you will "erase" the definition from the trait.
      More details about traits here: 
      [PHP Traits](https://www.php.net/manual/en/language.oop5.traits.php)
 4. The simplest definition requires no parameters for the attribute.
    Though the type of the property (getter or setter or both) is decided by
    the method signature:
    * If the method signature has **at least 1 parameter** and 
      **no return-type (or return-type is "void" or "never")** - then it's considered
      _SETTER_.
    * If the method signature has **no parameters** and **return-type is not defined, or
      return-type is set to anything except "void" or "never"** - then it's considered
      _GETTER_.
    * **If both non-contradicting conditions above are applied** - then it's considered
      _BOTH_. In this case the second parameter is always provided to the method with the 
      call-type string (`get` or `set`). This means that the method will be used for both 
      setting and getting, and you need to define if-else condition that will set 
      value for `set` and return value for `get` call-type.
 5. In case if `type` parameter for attribute is specified - then it has precedence 
    before all above, and it defines the type how the method will be considered.
 6. If you have multiple properties named the same way and of the same type - the first
    met suitable method will be used. (Try to avoid such situations with 
    multiple definitions of the same name and type).
 7. Name of the property is taken from `name` parameter of property attribute 
    if defined, otherwise the method name will be used instead.
 8. **Important:** The internal in-method-code `return` directive does not play role
    for the identifying type of the method (`get`). Only "return-type" in the signature
    of the method is being used.
 9. **Important:** Due to nature of attributes in PHP, if you override method that 
     is marked as Property, the new definition must have Property attribute as well, 
     otherwise you will lose the property (**Hint:** It's, funny, but it's unintentional
     functionality to disable or even redefine aspects of properties from parents through 
     the overriding!).

### Performance note!
The whole framework is built on the consideration of efficiency. And property
implementation was heavily optimized to reduce as much efficiency distinction 
as possible with a direct usage of "method call" and comparable popular framework 
functionality (**Yii2**).

The general borderline - is efficiency of PHP mechanisms, any usage of a field 
through `__set` and `__get` have it's drawbacks when compared to direct method call. 

At this point efficiency is **almost 1:1** (+/- 10%) with Yii2 getters and setters.
In some cases the php-simputils framework Properties might work even quicker than 
Yii2 getters and setters.

In the most cases, any of your code implementations will play much more role than 
the performance compromises of Properties.

### Some suggested good practices

 1. It's better to have consistent definition style at least across the same project.
 2. It's a good practice to specify "name" parameter always for `Property` 
    and `PropertyBatch`.
 3. __-more to come-__

### Code examples

#### Getters only
```php
    use spaf\simputils\attributes\Property;
    /**
     * @property ?string $getPropOne
     * @property ?string $prop_two
     * @property ?string $prop_three
     * @property ?string $getPropFour
     * @property ?string $getPropFive
     */
    class A {
        use PropertiesTrait;

        private $x_field = 'test A';

        //// Getters only
        
        #[Property]
        public function getPropOne() {
            return $this->x_field;
        }
    
        #[Property('prop_two')]
        public function getPropTwo() {
            return $this->x_field . 'prop_two';
        }
    
        #[Property('prop_three', 'get')]
        public function getPropThree() {
            return $this->x_field . 'prop_three';
        }
    
        #[Property(type: Property::TYPE_GET)]
        public function getPropFour() {
            return $this->x_field . 'prop_four';
        }
    
        #[Property]
        public function getPropFive(): string {
            return $this->x_field . 'prop_five';
        }
```

#### Setters only
```php
    /**
     * @property ?string $setPropOne
     * @property ?string $prop_two
     * @property ?string $prop_three
     * @property ?string $setPropFour
     */
    use spaf\simputils\attributes\Property;
    class B {
        use PropertiesTrait;
        
        private $x_field = 'test B';
        
        #[Property]
        public function setPropOne($val) {
            $this->x_field = $val;
        }
    
        #[Property('prop_two')]
        public function setPropTwo($val) {
            $this->x_field = $val;
        }
    
        #[Property('prop_three', 'set')]
        public function setPropThree($val) {
            $this->x_field = $val;
        }
    
        #[Property(type: Property::TYPE_SET)]
        public function setPropFour($val) {
            $this->x_field = $val;
        }
    }
```

#### Getter and Setter with a single method
```php
    use spaf\simputils\attributes\Property;
    /**
     * @property ?string $getSetPropOne
     * @property ?string $prop_two
     * @property ?string $prop_three
     * @property ?string $getSetPropFour
     */
    class C {
        use PropertiesTrait;
        
        private $x_field = 'test C';
        
        #[Property]
        public function getSetPropOne($val, $type): ?string {
            if ($type === 'get') {
                return $this->x_field;
            } else {
                $this->x_field = $val;
            }
        }
    
        #[Property('prop_two')]
        public function getSetPropTwo($val, $type): ?string {
            if ($type === 'get') {
                return $this->x_field;
            } else {
                $this->x_field = $val;
            }
        }
    
        #[Property('prop_three', 'both')]
        public function getSetPropThree($val, $type) {
            if ($type === 'get') {
                return $this->x_field;
            } else {
                $this->x_field = $val;
            }
        }
    
        #[Property(type: Property::TYPE_BOTH)]
        public function getSetPropFour($val, $type) {
            if ($type === 'get') {
                return $this->x_field;
            } else {
                $this->x_field = $val;
            }
        }
    }
```

## PropertyBatch
**Important:** It's not allowed to call properties defined through `Property` and `PropertyBatch`
inside of methods marked with `PropertyBatch`. If you need that functionality - use the 
direct method call of the marked property. It's unresolvable problem of "chicken and egg" 
(at least in current implementation!). The `Property` has not such problem due to simpler 
name resolution, than `PropertyBatch`.


## Future functionality

 * Planned to implement `Property` attribute for `fields`. For thin access control.
