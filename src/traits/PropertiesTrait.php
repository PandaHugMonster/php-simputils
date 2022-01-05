<?php

namespace spaf\simputils\traits;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use spaf\simputils\attributes\DebugHide;
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
	#[DebugHide]
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
	 * Prepares reflection objects that will be used for Properties
	 *
	 * @return array
	 */
	private function getAllTheLastMethodsAndProperties() {
		$class_reflection = new ReflectionClass($this);
		$res = [];
		// Progressing from original class, back to the root classes
		while ($class_reflection) {
			$stub = array_merge(
				$class_reflection->getMethods(),
				$class_reflection->getProperties()
			);
			foreach ($stub as $item) {
				if (empty($res[$item->getName()])) {
					$res[$item->getName()] = $item;
				}
			}
			$class_reflection = $class_reflection->getParentClass();
		}
		return $res;
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

		// TODO Question of efficiency!?
		$applicable_items = $this->getAllTheLastMethodsAndProperties();

		// TODO Integrate this filter into method above?
		$applicable_attribute_classes = [PropertyBatch::class, Property::class];

		$already_defined = [];

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
						if (in_array($name, $already_defined)) {
							// NOTE Skipping already found methods, so the parent stuff
							//      would not overwrite/return data

							continue;
						}
						$already_defined[] = $name;

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

	/**
	 *
	 * @codeCoverageIgnore Unfinished
	 * @return array|null
	 */
	public function __debugInfo(): ?array {
		$res = [];

		// NOTE If the whole class is marked
		$self_class = new ReflectionObject($this);
		if ($self_class->getAttributes(DebugHide::class) ?? false) {
			return ['-- CLASS IS SILENCED --'];
		}

		$it_items = $this->getAllTheLastMethodsAndProperties();
		$batch_array_of_prop_types = [PropertyBatch::TYPE_GET, PropertyBatch::TYPE_BOTH];
		$property_array_of_prop_types = [Property::TYPE_GET, Property::TYPE_BOTH];

		foreach ($it_items as $item) {
			$prefix = null;
			$name = $item->getName();
			$ta = null; // target attribute
			$value = null;
			$is_show_instead_set = false;

			/** @var ReflectionMethod|ReflectionProperty $item */
			if ($item->isStatic()) {
				// FIX  Implement options in InitConfig
				$prefix = 'static::';
				continue;
			}

			foreach ($item->getAttributes() as $attr) {
				$dh = null;
				if ($attr->getName() === DebugHide::class) {
					if ($dh = $attr->newInstance()) {
						/** @var DebugHide $dh */
						// NOTE Don't optimize or reformat this code block.
						//      It should not be invoked if the "DebugHide" is being used.
						if ($dh->hide_all) {
							// Skipping the whole field output
							continue 2;
						} else {
							$value = $dh->show_instead ?? '****';
							$is_show_instead_set = true;
						}
					}
				} else if (empty($ta)) {
					$ta = $attr;
				}
			}

			if ($item instanceof ReflectionProperty) {
				if (!empty($ta) && $ta->getName() === PropertyBatch::class) {
					// NOTE PropertyBatch from method

					[$expected_names, $_] = PropertyBatch::expectedNamesAndDefaultValues(
						$this, $item, $ta
					);
					$access_type = PropertyBatch::accessType($ta);

					if (in_array($access_type, $batch_array_of_prop_types)) {
						foreach ($expected_names as $expected_name) {
							$res["{$expected_name}"] = $is_show_instead_set
								?$value
								:$this->{$expected_name};
						}
					}
				} else {
					// NOTE Real PHP native property
					$item->setAccessible(true);
					$res["{$prefix}{$name}"] = $item->getValue($this);
					$item->setAccessible(false);
				}
			} else if ($item instanceof ReflectionMethod) {
				// NOTE Property/PropertyBatch from method

				if (!empty($ta) && $ta->getName() === Property::class) {
					$expected_name = Property::expectedName($item, $attr);
					$method_type = Property::methodAccessType($item, $attr);

					if (in_array($method_type, $property_array_of_prop_types)) {
						$res["{$expected_name}"] = $is_show_instead_set
							?$value
							:$this->{$expected_name};
					}
				} else if (!empty($ta) && $ta->getName() === PropertyBatch::class) {
					[$expected_names, $_] = PropertyBatch::expectedNamesAndDefaultValues(
						$this, $item, $ta
					);
					$access_type = PropertyBatch::accessType($ta);

					if (in_array($access_type, $batch_array_of_prop_types)) {

						foreach ($expected_names as $expected_name) {
							$res["{$expected_name}"] = $is_show_instead_set
								?$value
								:$this->{$expected_name};;
						}
					}
				}
			}
		}
		return $res;
	}
}
