<?php

namespace spaf\simputils\traits;

use spaf\simputils\models\Box;
use spaf\simputils\PHP;
use function is_null;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;

/**
 * MetaMagic trait
 *
 * MetaMagic functionality is a "framework-level" implementation of similar to "php magic methods"
 * functionality. But instead of 2 underscore signs, 3 are being used.
 *
 * For the purpose of logic we use "deserialize" as the opposite of "serialize", while default
 * PHP methods are: {@see \serialize()} and {@see \unserialize()}. Maybe there is not much of
 * a difference, but some dictionaries does not know "unserialize" word, while "deserialize"
 * is available more often in the dictionaries.
 *
 * So consider it as "a personal judgement call". If for some reason I will receive enough requests
 * from people to change it - then probably would be possible to switch from "deserialize"
 * to "unserialize".
 *
 * @package spaf\simputils\traits
 */
trait MetaMagic {

	/**
	 * Converts object to JSON string
	 *
	 * @param bool  $with_class Default to false, whether the additional "_class" value
	 *                          should be added
	 * @param ?bool $pretty     Multi-line pretty json
	 *
	 * @return string
	 */
	public function toJson(bool $with_class = false, ?bool $pretty = null): string {
		$data = $this->toArray($with_class);
		$flags = 0;
		if (is_null($pretty)) {
			if (isset(static::$is_json_pretty) && static::$is_json_pretty === true) {
				$flags |= JSON_PRETTY_PRINT;
			}
		} else if ($pretty) {
			$flags |= JSON_PRETTY_PRINT;
		}
		return json_encode($data, $flags);
	}

	/**
	 * Create an object from JSON string
	 *
	 * Uses "_class" to determine which class should be used for the newly created object.
	 * If it's not defined, by default the current static class will be used as the class for
	 * the object.
	 *
	 * @param string $json Json string
	 *
	 * @return static
	 * @throws \ReflectionException Reflection issues
	 */
	public static function fromJson(string $json): static {
		$data = json_decode($json, true);
		return static::fromArray($data);
	}

	/**
	 * Represents object as an array
	 *
	 * TODO Currently only recursively
	 *
	 *
	 * Some good examples of the Box and simple array difference:
	 * ```php
	 *
	 *      // Normal behaviour (Box is enabled by default)
	 *      $a = PHP::version();
	 *
	 *      $b = $a->toArray();
	 *
	 *      echo "\$a is {$a->obj_type}\nand\n\$b is {$b->obj_type}";
	 *      // The output would be something like:
	 *      //      $a is spaf\simputils\models\Version
	 *      //      and
	 *      //      $b is spaf\simputils\models\Box
	 *
	 *      // Disabling usage of Box instead of normal array
	 *      PHP::$use_box_instead_of_array = false;
	 *
	 *      // Adjusted behaviour (Box is DISABLED now)
	 *      $b = $a->toArray();
	 *
	 *      echo "\$a is {$a->obj_type}\nand\n\$b is ".PHP::type($b);
	 *      // The output would be something like:
	 *      //      $a is spaf\simputils\models\Version
	 *      //      and
	 *      //      $b is array
	 *
	 * ```
	 *
	 * @param bool $with_class Result will contain full class name
	 * @todo implement recursive and non recursive approach
	 * @return Box|array
	 */
	public function toArray(bool $with_class = false): Box|array {
		$res = json_decode(json_encode($this), true);
		if ($with_class)
			$res[PHP::$serialized_class_key_name] = static::class;
		if (PHP::$use_box_instead_of_array) {
			$res = new Box($res);
		}
		return $res;
	}

	/**
	 * Create an object from array
	 *
	 * @param array $data Array data for the class
	 *
	 * @return static
	 * @throws \ReflectionException Reflection issues
	 */
	public static function fromArray(array $data): static {
		$class = static::class;
		if (!empty($data[PHP::$serialized_class_key_name])) {
			$class = $data[PHP::$serialized_class_key_name];
			unset($data[PHP::$serialized_class_key_name]);
		}

		$obj = $class::createDummy();
		return static::_metaMagic($obj, '___setup', $data);
	}

	/**
	 * Creates the dummy object of the class (instance creation without constructor)
	 *
	 * @return static
	 *
	 * @throws \ReflectionException Reflection Exception
	 */
	public static function createDummy(): static {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return PHP::createDummy(static::class);
	}

	/**
	 * Setup object with fields values from assoc-array
	 *
	 * @param array $data Setup data
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
	protected function ___serialize(): Box|array {
		return $this->toArray(PHP::$serialization_mechanism === PHP::SERIALIZATION_TYPE_JSON);
	}

	/**
	 * De-serialization meta-magic method
	 *
	 * @param array $data Data received during deserialization
	 *
	 * @return $this
	 */
	protected function ___deserialize(Box|array $data): static {
		if (isset($data[PHP::$serialized_class_key_name]))
			unset($data[PHP::$serialized_class_key_name]);
		return static::_metaMagic($this, '___setup', $data);
	}

	/**
	 * MetaMagic controlling method
	 *
	 * This method in the most cases is not needed for you. It does "MetaMagic" possible.
	 *
	 * Basically does call to related "meta-magical" (triple-underscored) methods. Please refrain
	 * from using it, to do not accidentally mess with the framework compatibility issues.
	 *
	 * As well it's strongly recommended not using "meta-magical" (triple-underscored) methods
	 * directly either. In the worst case scenario, if you really need the functionality, use calls
	 * to "meta-magical" (triple-underscored) methods through this method.
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
	 *  1.  This "meta-magical" solution allows setting even "private" and "protected" fields
	 *      (while the equivalent - can't)
	 *  2.  The "meta-magical" solution is flexible, so you (or somebody else) can redefine
	 *      {@see MetaMagic::___setup()} to improve logic of particular classes. So it's really
	 *      reasonable to use meta-magic in such cases.
	 *
	 *
	 * @param mixed ...$spell Any kind of simputils spells
	 *
	 * @see MetaMagic::___serialize() Serialization meta-magic
	 * @see MetaMagic::___deserialize() Deserialization meta-magic
	 * @see MetaMagic::___setup() Object fulfilling meta-magic
	 *
	 * @see https://www.php.net/manual/en/language.oop5.visibility.php#language.oop5.visibility-other-objects
	 *      Visibility of the "relatives"
	 *
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
	 * If not redefined, will cause meta-magical method {@see MetaMagic::___serialize()}
	 * to be used for serialize functionality.
	 *
	 * @return array
	 */
	public function __serialize(): array {
		return (array) static::_metaMagic($this, '___serialize');
	}

	/**
	 * Default php deserialization compatibility layer
	 *
	 * If not redefined, will cause meta-magical method {@see MetaMagic::___deserialize()}
	 * to be used for deserialize/unserialize functionality.
	 *
	 * **Important:** It's standard PHP serialization functionality and is not being
	 * related to the framework itself directly.
	 *
	 * @param array $data Data that is used for serialization
	 * @see https://www.php.net/manual/en/function.unserialize.php
	 * @return void
	 */
	public function __unserialize(array $data): void {
		static::_metaMagic($this, '___deserialize', $data);
	}
}
