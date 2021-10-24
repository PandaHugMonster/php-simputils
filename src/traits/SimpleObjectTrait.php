<?php


namespace spaf\simputils\traits;


use ReflectionClass;
use spaf\simputils\PHP;
use spaf\simputils\Settings;
use function spl_object_id;

/**
 * Trait SimpleObjectTrait
 *
 * @property-read int $obj_id
 * @property-read string $obj_type
 *
 * @package spaf\simputils\traits
 */
trait SimpleObjectTrait {
	use PropertiesTrait;
//
//	/**
//	 * @param string $name Property name
//	 *
//	 * @return mixed
//	 * @throws \spaf\simputils\exceptions\PropertyAccessError Property access error
//	 */
//	public function __get(string $name): mixed {
//		$internal_name = self::preparePropertyName(self::GOS_GET, $name);
//		$opposite_internal_name = self::preparePropertyName(self::GOS_SET, $name);
//
//		if (method_exists($this, $internal_name)) {
//			return $this->$internal_name();
//		} elseif (method_exists($this, $opposite_internal_name)) {
//			throw new PropertyAccessError('Property "' . $name . '" is write-only.');
//		}
//
//		throw new PropertyAccessError('Can\'t get property "'.$name.'". No such property.');
//	}
//
//	/**
//	 * @param string $name  Property name
//	 * @param mixed  $value Value to set
//	 *
//	 * @throws \spaf\simputils\exceptions\PropertyAccessError Property access error
//	 */
//	public function __set(string $name, mixed $value): void {
//		$internal_name = self::preparePropertyName(self::GOS_SET, $name);
//		$opposite_internal_name = self::preparePropertyName(self::GOS_GET, $name);
//
//		if (method_exists($this, $internal_name))
//			$this->$internal_name($value);
//		elseif (method_exists($this, $opposite_internal_name))
//			throw new PropertyAccessError('Property "'.$name.'" is read-only.');
//		else
//			throw new PropertyAccessError('Can\'t set property "'.$name.'". No such property.');
//
//	}

	/**
	 * @param string $gos      Get or Set prefix
	 * @param string $property Property name
	 *
	 * @return string
	 */
	protected static function preparePropertyName(string $gos, string $property): string {
		$type_case = Settings::getSimpleObjectTypeCase();
		if ($type_case == Settings::SO_SNAKE_CASE || empty($type_case))
			return $gos.'_'.$property;
		else {
			return $gos.(ucfirst($property));
		}
	}

	/**
	 * @return int
	 */
	public function getObj_id(): int {
		return spl_object_id($this);
	}

	/**
	 * @return string
	 */
	public function getObj_type(): string {
		return PHP::type($this);
	}

	/**
	 * @return string
	 */
	public static function classShort(): string {
		return (new ReflectionClass(static::class))->getShortName();
	}

	/**
	 *
	 * @return string
	 */
	public function __toString(): string {
		if (static::$to_string_format_json) {
			return $this->toJson();
		}

		return 'Object <'.static::classShort().'#'.$this->objId().'>';
	}
}
