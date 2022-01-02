<?php

namespace spaf\simputils\traits;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use spaf\simputils\attributes\Property;
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\exceptions\PropertyAccessError;
use spaf\simputils\exceptions\PropertyDoesNotExist;
use spaf\simputils\special\PropertiesCacheIndex;
use function in_array;

/**
 * MARK Provide explanation for the traits messiness.
 * Why this trait is so messy?!
 *
 *
 * MARK Relocate this into documentation
 *
 * An efficiency of the property is improved to the level almost equal to calling `__get()`
 * magical method. Though, important to note, that the `__get()` method is 3 (up to 4) times slower
 * than the direct method call on the object. So these properties are as efficient as they can get,
 * unless PHP internally will not implement them with C-level optimization.
 *
 * This implementation as minimum not worse than having on market options for turning methods
 * into properties (through `__get()`).
 *
 * **Important:** It's strongly recommended to use {@see \spaf\simputils\generic\SimpleObject}
 * as your basic extending object, though the Properties were done flexibly standalone,
 * so you can use their functionality without any extension. Just use this trait in your class
 * (better use layer class or your own basic class before the target class). After that you can use
 * all the `Property*` attributes in the class and it's child classes.
 *
 * __After-note:__ Basically it's safe enough for performance to use `Properties`, though if you
 * have extremely complex and big monolith code (which is not a good thing in the most cases),
 * you might have some dropdowns of efficiency if compared to direct calls, but in the most cases
 * it will be so negligible, that almost always it would be much more efficient to fix/optimize
 * the "complexities" of your own solution/code first.
 *
 * TODO Implement normal PropertyReflection class!
 *
 * TODO Subject to even more optimization
 */
trait PropertiesTrait {

	// FIX  Public modifier is a temporary solution, due to external modification of the field
	public $____property_batch_storage = [];

	/**
	 * @param string $name
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get($name): mixed {
		if (
			$method_name = PropertiesCacheIndex
			::$index[static::class.'#'.$name.'#'.Property::TYPE_GET]
			?? false
		) {
			return $this->$method_name(null, Property::TYPE_GET, $name);
		}
		return $this->____prepareProperty($name, Property::TYPE_GET);
	}

	public function __set($name, $value): void {
		if (
			$method_name = PropertiesCacheIndex
			::$index[static::class.'#'.$name.'#'.Property::TYPE_SET]
			?? false
		) {
			$this->$method_name($value, Property::TYPE_SET, $name);
		} else {
			$this->____prepareProperty($name, Property::TYPE_SET, $value);
		}
	}

	public function __isset($name) {
		// FIX  Implementation is questionable. Urgently refactor!
		if (
			$method_name = PropertiesCacheIndex
			::$index[static::class.'#'.$name.'#'.Property::TYPE_GET]
			?? false
		) {
			return $this->$method_name(null, Property::TYPE_GET, $name);
		}
		return $this->____prepareProperty(
			$name, Property::TYPE_GET,
			check_and_do_not_call: true
		);
	}

	private function ____propertyBatchMethodGet($value, $type, $name): mixed {
		$settings = PropertiesCacheIndex::$property_settings[static::class.'#'.$name];
		$value_store_ref = $settings['storage'];
		if ($value_store_ref === PropertyBatch::STORAGE_SELF) {
			$value_store = &$this;
		} else {
			$value_store = &$this->$value_store_ref;
		}
		return $value_store[$name] ?? null;
	}

	private function ____propertyBatchMethodSet($value, $type, $name): void {
		$settings = PropertiesCacheIndex::$property_settings[static::class.'#'.$name];
		$value_store_ref = $settings['storage'];
		if ($value_store_ref === PropertyBatch::STORAGE_SELF) {
			$value_store = &$this;
		} else {
			$value_store = &$this->$value_store_ref;
		}
		$value_store[$name] = $value;
	}

	/**
	 * @param string $name
	 * @param string $call_type
	 * @param mixed $value
	 * @param bool $check_and_do_not_call
	 *
	 * @return bool
	 * @throws PropertyAccessError
	 * @throws PropertyDoesNotExist
	 */
	private function ____prepareProperty(
		string $name,
		string $call_type,
		mixed $value = null,
		bool $check_and_do_not_call = false
	): mixed {
		$sub = null;

		$class_reflection = new ReflectionClass($this);

		$applicable_items = array_merge(
			$class_reflection->getMethods(),
			$class_reflection->getProperties()
		);

		$applicable_attribute_classes = [PropertyBatch::class, Property::class];

		foreach ($applicable_items as $item) {
			/** @var ReflectionMethod|ReflectionProperty $item */
			/** @var \ReflectionAttribute $attr */

			foreach ($item->getAttributes() as $attr) {
				$attr_class = $attr->getName();
				if (in_array($attr_class, $applicable_attribute_classes)) {

					[$func_ref, $status] = call_user_func(
						[$attr_class, 'subProcess'],
						$this, $item, $attr, $name, $call_type
					);

					if ($status === true) {
						if ($check_and_do_not_call && $call_type !== Property::TYPE_SET) {
							// NOTE Relevant for `isset()`
							return true;
						}
						return $this->$func_ref($value, $call_type, $name);
					} else if ($status !== false && empty($sub)) {
						$sub = $status;
					}
					break;
				}
			}
		}

		if ($check_and_do_not_call) {
			// NOTE Relevant for `isset()`
			return false;
		}

		if (!empty($sub)) {
			throw new PropertyAccessError(
				'Property '.$name.' of "'.$sub.'" access'
			);
		}

		throw new PropertyDoesNotExist('No such property '.$name);
	}

	// FIX  If file does not exist, exception is raised, even though those properties should be
	//      skipped (content, etc.)
	public function __debugInfo(): ?array {
		$ref = new ReflectionObject($this);
		$res = [];
		foreach ($ref->getProperties() as $property) {
			$name = $property->name;

			$property->setAccessible(true);
			$value = $property->getValue($this);
			$property->setAccessible(false);

			$name = "\${$name}";
			if ($property->isStatic()) {
				$name = "static::{$name}";
			}

			$res[$name] = $value;
		}
		$ref_class = new ReflectionClass($this);
		$items = $ref_class->getMethods();
		foreach ($items as $item) {
			$attr = $item->getAttributes(Property::class)[0] ?? null;
			if (!empty($attr)) {
//				$expected_name = $this->_propertyExpectedName($item, $attr);
//				$method_type = $this->_propertyMethodAccessType($item, $attr);
				$expected_name = Property::expectedName($item, $attr);
				$method_type = Property::methodAccessType($item, $attr);

				if ($this->_debugOutputDisabled($item, $attr)) {
					$value = '<..skipped..>';
				} else {
					// Do not optimize additionally. This code must not be called if debugOutput
					// is disabled!
					$value = $this->{$expected_name};
				}
				if ($method_type === Property::TYPE_GET || $method_type === Property::TYPE_BOTH) {
					$res["\${$expected_name}"] = $value;
				}
			}
		}
		$items = array_merge($ref_class->getProperties(), $ref_class->getMethods());
		foreach ($items as $item) {
			$attr = $item->getAttributes(PropertyBatch::class)[0] ?? null;
			if (!empty($attr)) {
				[$expected_names, $_]
					= PropertyBatch::expectedNamesAndDefaultValues($this, $item, $attr);
				$access_type = PropertyBatch::accessType($attr);

				if ($access_type === Property::TYPE_GET || $access_type === Property::TYPE_BOTH) {
					foreach ($expected_names as $expected_name) {
						$res["\${$expected_name}"] = $this->{$expected_name};
					}
				}
			}
		}
		return $res;
	}

	private function _debugOutputDisabled($ref, ReflectionAttribute $attr): bool {
		$args = $attr->getArguments();
		return
			(isset($args['debug_output']) && $args['debug_output'] === false) ||
			(isset($args[2]) && $args[2] === false);
	}
}
