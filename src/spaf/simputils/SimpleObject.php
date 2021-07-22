<?php


namespace spaf\simputils;



use spaf\simputils\exceptions\PropertyAccessError;

/**
 * SimpleObject
 *
 * Basically represents the simplest object with "getter/setter" properties control,
 * and strict access check to those properties.
 *
 * It's not allowed to assign non-existing properties, so it's a bit more strict than
 * normal PHP Objects
 *
 * @todo Implement both "snake_case" format and "camelCase"
 *
 * @package spaf\simputils
 */
abstract class SimpleObject {

    private const GOS_GET = 'get';
    private const GOS_SET = 'set';

    public function __get(string $name) {
        $internal_name = self::prepare_property_name(self::GOS_GET, $name);
        $opposite_internal_name = self::prepare_property_name(self::GOS_SET, $name);

        if (method_exists($this, $internal_name))
            return $this->$internal_name();
        elseif (method_exists($this, $opposite_internal_name))
            throw new PropertyAccessError('Property "'.$name.'" is write-only.');

        throw new PropertyAccessError('Can\'t get property "'.$name.'". No such property.');
    }

    public function __set(string $name, $value): void {
        $internal_name = self::prepare_property_name(self::GOS_SET, $name);
        $opposite_internal_name = self::prepare_property_name(self::GOS_GET, $name);

        if (method_exists($this, $internal_name))
            $this->$internal_name($value);
        elseif (method_exists($this, $opposite_internal_name))
            throw new PropertyAccessError('Property "'.$name.'" is read-only.');
        else
            throw new PropertyAccessError('Can\'t set property "'.$name.'". No such property.');

    }

    protected static function prepare_property_name($gos, $property): string {
        return $gos.'_'.$property;
    }
}