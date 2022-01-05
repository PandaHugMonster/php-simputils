<?php


namespace spaf\simputils\traits;


use ReflectionClass;
use spaf\simputils\attributes\Property;
use spaf\simputils\PHP;
use function spl_object_id;

/**
 * Trait SimpleObjectTrait
 *
 * @property-read int $obj_id
 * @property-read string $obj_type
 * @property-read string $class_short
 *
 * @package spaf\simputils\traits
 */
trait SimpleObjectTrait {
	use PropertiesTrait;

	/**
	 * @return int
	 */
	#[Property('obj_id')]
	public function getObjId(): int {
		return spl_object_id($this);
	}

	/**
	 * @return string
	 */
	#[Property('obj_type')]
	public function getObjType(): string {
		return PHP::type($this);
	}

	/**
	 * TODO Describe fact of "fake static properties"
	 *
	 * @return string
	 */
	#[Property('class_short')]
	protected static function classShort(): string {
		return (new ReflectionClass(static::class))->getShortName();
	}

	/**
	 * This is just a shortcut of "clone $obj"
	 *
	 * @return static
	 */
	public function clone(): static {
		return clone $this;
	}

	/**
	 *
	 * @return string
	 */
	public function __toString(): string {
		if (static::$to_string_format_json) {
			return $this->toJson();
		}

		return 'Object <'.static::classShort().'#'.$this->obj_id.'>';
	}
}
