<?php

namespace spaf\simputils\traits;

use Exception;
use spaf\simputils\FS;
use spaf\simputils\models\Box;
use spaf\simputils\models\File;
use spaf\simputils\PHP;
use function get_object_vars;
use function is_array;
use function is_null;
use function is_object;
use function json_decode;
use function json_encode;
use function method_exists;
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
	 * **Important:** Efficiency of this method relies on the efficiency of
	 * {@see \spaf\simputils\traits\MetaMagic::toArray()}
	 *
	 *
	 * If you want to convert to Boxes, use {@see \spaf\simputils\traits\MetaMagic::toBox()}
	 *
	 * If you want to convert to native arrays, use
	 * {@see \spaf\simputils\traits\MetaMagic::toArray()}
	 *
	 * ##### Examples:
	 *
	 * ###### One-line version
	 * ```php
	 *  $v = new Version('1.2.3', 'My App');
	 *  echo $v->toJson();
	 * ```
	 * Output would be:
	 * ```json
	 * {"parser":{ },"software_name":"My App","major":1,"minor":2,"patch":3,
	 * "build_type":"","build_revision":null,"prefix":null,"postfix":null,"non_standard":null,
	 * "original_value":"1.2.3","original_strict":null}
	 * ```
	 *
	 * ###### Prettified version:
	 * ```php
	 *  $v = new Version('1.2.3', 'My App');
	 *  echo $v->toJson(true);
	 * ```
	 * Output would be:
	 * ```json
	 * {
	 *  "parser": { },
	 *  "software_name": "My App",
	 *  "major": 1,
	 *  "minor": 2,
	 *  "patch": 3,
	 *  "build_type": "",
	 *  "build_revision": null,
	 *  "prefix": null,
	 *  "postfix": null,
	 *  "non_standard": null,
	 *  "original_value": "1.2.3",
	 *  "original_strict": null
	 * }
	 * ```
	 *
	 * ###### Prettified + with class version:
	 * ```php
	 *  $v = new Version('1.2.3', 'My App');
	 *  echo $v->toJson(true, true);
	 * ```
	 *
	 * Output would be:
	 * ```json
	 * {
	 *  "parser": {
	 *      "#class": "spaf\\simputils\\components\\versions\\parsers\\DefaultVersionParser"
	 *  },
	 *  "software_name": "My App",
	 *  "major": 1,
	 *  "minor": 2,
	 *  "patch": 3,
	 *  "build_type": "",
	 *  "build_revision": null,
	 *  "prefix": null,
	 *  "postfix": null,
	 *  "non_standard": null,
	 *  "original_value": "1.2.3",
	 *  "original_strict": null,
	 *  "#class": "spaf\\simputils\\models\\Version"
	 * }
	 * ```
	 *
	 * Due to a bug in phpDocumentor that damages json output, the demonstrated outputs were
	 * slightly adjusted to workaround this bug.
	 *
	 * @param ?bool $pretty     Multi-line pretty json format
	 * @param bool  $with_class Default to false, whether the additional "#class" value
	 *                          should be added
	 *
	 * @return string
	 */
	public function toJson(?bool $pretty = null, bool $with_class = false): string {
		$data = $this->toArray(true, $with_class);
		$flags = null;
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
	 * Uses "#class" to determine which class should be used for the newly created object.
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
	 * Represents object as an array (not Box)
	 *
	 * This method turns the object (and if specified recursively the underlying objects)
	 * into array(s) (PHP native Arrays, not Boxes)
	 *
	 * **Important:** Keep in mind that it might be not the quickest solution in matter of
	 * speed of execution (at least in case of recursive call).
	 *
	 * If you want to convert to Boxes, use {@see \spaf\simputils\traits\MetaMagic::toBox()}
	 *
	 * If you want to convert to Json string, use {@see \spaf\simputils\traits\MetaMagic::toJson()}
	 *
	 * @param bool $recursively Do the conversion recursively
	 * @param bool $with_class  Result will contain full class name
	 *
	 * @return array
	 */
	public function toArray(bool $recursively = false, bool $with_class = false): array {
		$res = [];

		if ($this instanceof Box) {
			if ($recursively) {
				$res = $this->_iterateConvertObjectsAndArrays(
					(array) $this, $recursively, $with_class, false
				);
			} else {
				return (array) $this;
			}
		} else if (method_exists($this, '___extractFields')) {
			$fields = $this->___extractFields(true, false);
			$res = $recursively
				?$this->_iterateConvertObjectsAndArrays($fields, $recursively, $with_class, false)
				:$fields;
		} else {
			$res = json_decode(json_encode($this), true);
		}

		if ($with_class) {
			$res[PHP::$serialized_class_key_name] = static::class;
		}

		return $res;
	}

	/**
	 * Converts the object into box-array
	 *
	 * From non-Box objects fields and corresponding values are extracted, and packed
	 * into box-array.
	 *
	 * If box is the target object, it will be returned as is (exactly the same object),
	 * but all of it's values (objects and arrays) will be converted to Boxes.
	 *
	 * The packing of the fields relies on {@see \spaf\simputils\attributes\Extract}
	 * attribute.
	 *
	 * The {@see \spaf\simputils\attributes\DebugHide} attribute does not play role
	 * in extraction of the fields.
	 *
	 *
	 * If you want to convert to native arrays, use
	 * {@see \spaf\simputils\traits\MetaMagic::toArray()}
	 *
	 * If you want to convert to Json string, use {@see \spaf\simputils\traits\MetaMagic::toJson()}
	 *
	 * @param bool $recursively Recursively iterates through the sub-elements
	 *                          and converts those as well
	 * @param bool $with_class  Include class "key" => "value" reference
	 *
	 * @return \spaf\simputils\models\Box
	 * @throws \Exception Exception
	 */
	public function toBox(bool $recursively = false, bool $with_class = false): Box {
		$box_class = PHP::redef(Box::class);

		$sub = [];
		$is_box_already = $this instanceof Box;
		$res = $is_box_already
			?$this
			:new $box_class;
		/** @var Box $res */

		if ($this instanceof Box) {
			if ($recursively) {
				$sub = $this->_iterateConvertObjectsAndArrays(
					(array) $this, $recursively, $with_class
				);

				if (PHP::classUsesTrait($this, ArrayReadOnlyAccessTrait::class)) {
					$res = new $box_class;
				}
			} else {
				return $this;
			}
		} else if (method_exists($this, '___extractFields')) {
			$fields = $this->___extractFields(true, false);
			$sub = $recursively
				?$this->_iterateConvertObjectsAndArrays($fields, $recursively, $with_class)
				:$fields;
		} else {
			// TODO Maybe implement mechanism to enforce this kind of conversion
			$sub = json_decode(json_encode($this), true);
		}

		if ($with_class) {
			$sub[PHP::$serialized_class_key_name] = static::class;
		}

		return $res->load($sub);
	}

	private function _iterateConvertObjectsAndArrays(
		array $fields,
		bool $recursively,
		bool $with_class,
		bool $use_box = true,
	): Box|array {
		$box_class = PHP::redef(Box::class);
		$res = $use_box
			?new $box_class
			:[];
		foreach ($fields as $k => $v) {
			if (is_object($v) && method_exists($v, 'toBox')) {
				$sub = $v->toBox($recursively, $with_class);
				$res[$k] = $sub;
			} else if ($wut = is_array($v)) {
				$res[$k] = $this->_iterateConvertObjectsAndArrays($v, $recursively, false);
			} else {
				$res[$k] = $v;
			}
		}
		return $res;
	}

	/**
	 * Copies state of this object to the new object of specified class
	 *
	 * For the purpose of migrating the parent narrow class object, to the child wider
	 * class object.
	 *
	 * @param string|object $class_or_object          Class/Object that should be filled up
	 *                                                with data
	 * @param bool          $strict_inheritance_check Additional check to make sure the provided
	 *                                                class or object is a child from this one.
	 *                                                By default is true
	 *
	 * @return object Always returns a new object of type provided as a first argument
	 * @throws \Exception
	 */
//	public function expandTo(
//		string|object $class_or_object,
//		bool $strict_inheritance_check = true
//	): object {
//		if (Str::is($class_or_object)) {
//			$obj = new $class_or_object();
//		} else {
//			$obj = $class_or_object;
//		}
//		if ($strict_inheritance_check) {
//			if (!PHP::isClassIn($obj, $this)) {
//				throw new Exception('Expanding object strict inheritance check failed');
//			}
//		}
//
//		static::_metaMagic($obj, '___setup', $this->toArray());
//
//		return $obj;
//	}

	public static function expandFrom(
		object $parent,
		?object $child = null,
		bool $strict_inheritance_check = true
	): object {
		$obj = $child ?? static::createDummy();
		if ($strict_inheritance_check) {
			if (!PHP::isClassIn($parent, $obj)) {
				throw new Exception('Expanding object strict inheritance check failed');
			}
		}

		static::_metaMagic($obj, '___setup', get_object_vars($parent));

		return $obj;
	}

//	/**
//	 * To a normal PHP array
//	 *
//	 * @inheritdoc
//	 *
//	 * @param bool $with_class Pack with class, default is "false"
//	 *
//	 * @return array
//	 */
//	public function toArray(bool $with_class = false, bool $recursively = true): array {
//		if ($recursively) {
//			$res = [];
//			foreach ($this as $key => $val) {
//				if ($val instanceof Box) {
//					$res[$key] = $val->toArray(recursively: $recursively);
//				} else {
//					// FIX Implement meta-magic here, and improve "toBox()", "toArray" and "toJson"
//					$res[$key] = $val;
//				}
//			}
//		} else {
//			$res = (array) $this;
//		}
//
//		if ($with_class)
//			$res[PHP::$serialized_class_key_name] = static::class;
//		return $res;
//	}

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

	public static function createFrom(File|string $file) {
		$file = FS::file($file);
		$obj = static::createDummy();
		$content = $file->content ?? [];
		return static::_metaMagic($obj, '___setup', $content);
	}

	protected static function ___l10n(array $data) {
		$prefix = 'l10n';
		foreach ($data as $k => $v) {
			static::${"{$prefix}_{$k}"} = $v;
		}
	}

	/**
	 * Setup object with fields values from assoc-array
	 *
	 * @param array $data Setup data
	 *
	 * @return $this
	 */
	protected function ___setup(array $data): static {
		foreach ($data as $key => $val) {
			if (is_array($val) && !empty($val[PHP::$serialized_class_key_name])) {
				$obj = PHP::createDummy($val[PHP::$serialized_class_key_name]);
				unset($val[PHP::$serialized_class_key_name]);
				$val = $obj->___setup($val);
			}
			$this->$key = $val;
		}
		return $this;
	}

	/**
	 * Serialization meta-magic method
	 *
	 * @return Box|array
	 */
	protected function ___serialize(): Box|array {
		return $this->toArray(
			PHP::$serialization_mechanism === PHP::SERIALIZATION_TYPE_JSON,
			true
		);
	}

	/**
	 * De-serialization meta-magic method
	 *
	 * @param Box|array $data Data received during deserialization
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
	 * @see MetaMagic::___l10n() Object fulfilling meta-magic
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
			'___l10n' => $context::___l10n(...$spell),
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
