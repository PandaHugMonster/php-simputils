<?php


namespace spaf\simputils\traits;


use spaf\simputils\exceptions\PropertyAccessError;
use spaf\simputils\Settings;

/**
 * Trait SimpleObjectTrait
 * @package spaf\simputils\traits
 */
trait SimpleObjectTrait {

	/**
	 * @param string $name
	 *
	 * @return mixed
	 * @throws \spaf\simputils\exceptions\PropertyAccessError
	 */
	public function __get(string $name): mixed {
		$internal_name = self::prepare_property_name(self::GOS_GET, $name);
		$opposite_internal_name = self::prepare_property_name(self::GOS_SET, $name);

		if (method_exists($this, $internal_name))
			return $this->$internal_name();
		elseif (method_exists($this, $opposite_internal_name))
			throw new PropertyAccessError('Property "'.$name.'" is write-only.');

		throw new PropertyAccessError('Can\'t get property "'.$name.'". No such property.');
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws \spaf\simputils\exceptions\PropertyAccessError
	 */
	public function __set(string $name, mixed $value): void {
		$internal_name = self::prepare_property_name(self::GOS_SET, $name);
		$opposite_internal_name = self::prepare_property_name(self::GOS_GET, $name);

		if (method_exists($this, $internal_name))
			$this->$internal_name($value);
		elseif (method_exists($this, $opposite_internal_name))
			throw new PropertyAccessError('Property "'.$name.'" is read-only.');
		else
			throw new PropertyAccessError('Can\'t set property "'.$name.'". No such property.');

	}

	/**
	 * @param string $gos
	 * @param string $property
	 *
	 * @return string
	 */
	protected static function prepare_property_name(string $gos, string $property): string {
		$type_case = Settings::get_simple_object_type_case();
		if ($type_case == Settings::SO_SNAKE_CASE || empty($type_case))
			return $gos.'_'.$property;
		else {
			return $gos.(ucfirst($property));
		}
	}

}