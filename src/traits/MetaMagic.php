<?php

namespace spaf\simputils\traits;

use ReflectionClass;
use spaf\simputils\PHP;
use function json_decode;
use function json_encode;

/**
 * MetaMagic trait
 *
 * MetaMagic functionality is a "framework-level" implementation of similar to "php magic methods" functionality.
 * But instead of 2 underscore signs, 3 are being used.
 *
 * For the purpose of logic we use "deserialize" as the opposite of "serialize", while default PHP methods are:
 * {@see \serialize()} and {@see \unserialize()}. Maybe there is not much of a difference, but some dictionaries does not know
 * "unserialize" word, while "deserialize" is available more often in the dictionaries.
 *
 * So consider it as "a personal judgement call". If for some reason I will receive enough requests
 * from people to change it - then probably would be possible to switch from "deserialize" to "unserialize".
 *
 * @package spaf\simputils\traits
 */
trait MetaMagic {

	/**
	 * Converts object to JSON string
	 *
	 * @param bool $with_class Default to false, whether the additional "_class" value should be added
	 *
	 * @return string
	 */
	public function toJson(bool $with_class = false): string {
		$data = $this->toArray($with_class);
		return json_encode($data);
	}

	/**
	 * Create an object from JSON string
	 *
	 * Uses "_class" to determine which class should be used for the newly created object. If it's not defined,
	 * by default the current static class will be used as the class for the object.
	 *
	 * @param string $json
	 *
	 * @return static
	 * @throws \ReflectionException
	 */
	public static function fromJson(string $json): static {
		$data = json_decode($json, true);
		return static::fromArray($data);
	}

	/**
	 * Represents object as array
	 *
	 * @param bool $with_class
	 *
	 * @return array
	 */
	public function toArray(bool $with_class = false): array {
		$res = json_decode(json_encode($this), true);
		if ($with_class)
			$res[PHP::$serialized_class_key_name] = static::class;
		return $res;
	}

	/**
	 * Create an object from array
	 *
	 * @param array $data
	 *
	 * @return static
	 * @throws \ReflectionException
	 */
	public static function fromArray(array $data): static {
		$class = static::class;
		if (!empty($data[PHP::$serialized_class_key_name])) {
			$class = $data[PHP::$serialized_class_key_name];
			unset($data[PHP::$serialized_class_key_name]);
		}
		$reflection_class = new ReflectionClass($class);
		$obj = $reflection_class->newInstanceWithoutConstructor();
		return static::_metaMagic($obj, '___setup', $data);
	}

	/**
	 * Setup object with fields values from assoc-array
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	protected function ___setup(array $data): static {
		foreach ($data as $key => $val)
			$this->$key = $val;
		return $this;
	}

	/**
 	 * Serialization meta-magic method
	 *
	 * @return array
	 */
	protected function ___serialize(): array {
		return $this->toArray(PHP::$serialization_mechanism == PHP::SERIALIZATION_TYPE_JSON);
	}

	/**
	 * De-serialization meta-magic method
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	protected function ___deserialize(array $data): static {
		if (isset($data[PHP::$serialized_class_key_name]))
			unset($data[PHP::$serialized_class_key_name]);
		return static::_metaMagic($this, '___setup', $data);
	}

	/**
	 * MetaMagic controlling method
	 *
	 * This method in the most cases is not needed for you. It does "MetaMagic" possible.
	 *
	 * Basically does call to related "meta-magical" (triple-underscored) methods. Please refrain from using it, to do not
	 * accidentally mess with the framework compatibility issues.
	 *
	 * As well it's strongly recommended not using "meta-magical" (triple-underscored) methods directly either.
	 * In the worst case scenario, if you really need the functionality, use calls to "meta-magical" (triple-underscored) methods
	 * through this method
	 *
	 * Example of usage:
	 * ```php
	 *  $data = [
	 *      'field_1' => 'Value 1',
	 *      'field_2' => 'Value 2',
	 *      'field_3' => 'Value 3',
	 *      'field_4' => 'Value 4',
	 *  ];
	 *
	 *  // This:
	 *  MetaMagic::_metaMagic($obj, '___setup', $data);
	 *
	 *  // is equivalent to (in the most cases, if not redefined):
	 *  foreach ($data as $key => $val)
	 *      $obj->$key = $val;
	 * ```
	 * A few important notes:
	 *  1.  This "meta-magical" solution allows setting even "private" and "protected" fields (while the equivalent - can't)
	 *  2.  The "meta-magical" solution is flexible, so you (or somebody else) can redefine {@see MetaMagic::___setup()}
	 *      to improve logic of particular classes. So it's really reasonable to use meta-magic in such cases.
	 *
	 *
	 * @param ...$spell
	 *
	 * @see MetaMagic::___serialize() Serialization meta-magic
	 * @see MetaMagic::___deserialize() Deserialization meta-magic
	 * @see MetaMagic::___setup() Object fulfilling meta-magic
	 * @see https://www.php.net/manual/ru/language.oop5.visibility.php#language.oop5.visibility-other-objects Visibility of the "relatives"
	 * @return mixed
	 */
	public static function _metaMagic(...$spell): mixed {
		$context = $spell[0];
		$endpoint = $spell[1];
		array_shift($spell);
		array_shift($spell);
		$res = match ($endpoint) {
			'___serialize' => $context->___serialize(),
			'___deserialize' => $context->___deserialize(...$spell),
			'___setup' => $context->___setup(...$spell),
		};
		return $res;
	}

	// Compatibility layers

	/**
	 * Default php serialization compatibility layer
	 *
	 * If not redefined, will cause meta-magical method {@see MetaMagic::___serialize()} to be used for serialize functionality.
	 *
	 * @return array
	 */
	public function __serialize(): array {
		return static::_metaMagic($this, '___serialize');
	}

	/**
	 * Default php deserialization compatibility layer
	 *
	 * If not redefined, will cause meta-magical method {@see MetaMagic::___deserialize()} to be used
	 * for deserialize/unserialize functionality.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function __unserialize(array $data): void {
		static::_metaMagic($this, '___deserialize', $data);
	}

}