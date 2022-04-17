<?php /** @noinspection PhpMissingParamTypeInspection */

namespace spaf\simputils\traits;

use ArrayObject;
use Closure;
use Error;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use ReflectionUnionType;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Extract;
use spaf\simputils\attributes\Property;
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\exceptions\PropertyDoesNotExist;
use spaf\simputils\exceptions\PropertyIsReadOnly;
use spaf\simputils\exceptions\PropertyIsWriteOnly;
use spaf\simputils\generic\BasicOutputControlAttribute;
use spaf\simputils\models\Box;
use spaf\simputils\PHP;
use spaf\simputils\special\CommonMemoryCacheIndex;
use spaf\simputils\special\PropertiesCacheIndex;
use function get_parent_class;
use function in_array;
use function is_null;

/**
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

	// TODO Public modifier is a temporary solution, due to external modification of the field
	#[DebugHide]
	#[Extract(false)]
	public $_simp_utils_property_batch_storage = [];

	/**
	 * @param string $name Name of the property
	 *
	 * @return mixed
	 * @throws \spaf\simputils\exceptions\PropertyDoesNotExist Property does not exist
	 * @throws \spaf\simputils\exceptions\PropertyIsReadOnly   Property is read-only
	 * @throws \spaf\simputils\exceptions\PropertyIsWriteOnly  Property is write-only
	 */
	public function __get($name) {
		$ref = static::class.'#'.$name.'#'.Property::TYPE_GET;
		if ($method_name = PropertiesCacheIndex::$index[$ref] ?? false) {
			return $this->$method_name(null, Property::TYPE_GET, $name);
		}
		try {
			return $this->_simpUtilsPrepareProperty($name, Property::TYPE_GET);
		} catch (PropertyIsReadOnly | PropertyDoesNotExist $e) {
			try {
				/** @noinspection PhpUndefinedMethodInspection */
				return parent::__get($name);
			} catch (Error) {
				/** @noinspection PhpUnhandledExceptionInspection */
				throw $e;
			}
		}
	}

	public function __set($name, $value) {
		$ref = static::class.'#'.$name.'#'.Property::TYPE_SET;
		if ($method_name = PropertiesCacheIndex::$index[$ref] ?? false) {
			$this->$method_name($value, Property::TYPE_SET, $name);
		} else {
			try {
				$this->_simpUtilsPrepareProperty($name, Property::TYPE_SET, $value);
			} catch (PropertyIsReadOnly | PropertyDoesNotExist $e) {
				try {
					/** @noinspection PhpUndefinedMethodInspection */
					parent::__set($name, $value); // @codeCoverageIgnore
				} catch (Error) { // @codeCoverageIgnore
					/** @noinspection PhpUnhandledExceptionInspection */
					throw $e; // @codeCoverageIgnore
				}
			}
		}
	}

	public function __isset($name) {
		$type = Property::TYPE_GET;
		$ref = static::class.'#'.$name.'#'.$type;
		if ($method_name = PropertiesCacheIndex::$index[$ref] ?? false) {
			return $this->$method_name(null, $type, $name);
		}
		$res = $this->_simpUtilsPrepareProperty($name, $type, check_and_do_not_call: true);

		if (!$res) {
			try {
				/** @noinspection PhpUndefinedMethodInspection */
//				if (get_parent_class() && method_exists(parent::class, '__isset')) {
				if (get_parent_class()) {
					if (get_parent_class() !== ArrayObject::class) { // @codeCoverageIgnore
						$res = parent::__isset($name);
					}
				}
			} catch (Error $e) { // @codeCoverageIgnore
				/** @noinspection PhpUnhandledExceptionInspection */
				throw $e; // @codeCoverageIgnore
			}
		}

		return $res;
	}

	private function _simpUtilsPropertyBatchMethodGet($value, $type, $name): mixed {
		$settings = PropertiesCacheIndex::$property_settings[static::class.'#'.$name];
		$value_store_ref = $settings['storage'];
		if ($value_store_ref === PropertyBatch::STORAGE_SELF) {
			$value_store = &$this;
		} else {
			$value_store = &$this->$value_store_ref;
		}
		return $value_store[$name] ?? null;
	}

	private function _simpUtilsPropertyBatchMethodSet($value, $type, $name): void {
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
	 * @param string $name                  Called property name
	 * @param string $call_type             Call type ('set' or 'get')
	 * @param mixed  $value                 Value
	 * @param bool   $check_and_do_not_call If true, then value will not be prepared (
	 *                                      relevant only for {@see __isset()})
	 *
	 * @return bool
	 * @throws \spaf\simputils\exceptions\PropertyDoesNotExist Property does not exist
	 * @throws \spaf\simputils\exceptions\PropertyIsReadOnly   Property is read-only
	 * @throws \spaf\simputils\exceptions\PropertyIsWriteOnly  Property is write-only
	 */
	private function _simpUtilsPrepareProperty(
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
			/** @var \ReflectionMethod|\ReflectionProperty $item */

			foreach ($item->getAttributes() as $attr) {

				$attr_class = $attr->getName();
				if (in_array($attr_class, $applicable_attribute_classes)) {
					[$func_ref, $status, $name] = call_user_func(
						[$attr_class, 'subProcess'],
						$this, $item, $attr, $name, $call_type
					);

					if ($status === true) {
						if (in_array($name, $already_defined)) {
							// NOTE Skipping already found methods, so the parent stuff
							//      would not overwrite/return data

							continue; // @codeCoverageIgnore
						}
						$already_defined[] = $name;

						if ($check_and_do_not_call) {
							// NOTE Relevant for `isset()`
							return true;
						}

						if ($call_type === Property::TYPE_SET) {
							// NOTE Validation
							$validator = $this->_simpUtilsGetValidator($item, $attr, $call_type);
							if ($validator) {
								$value = $validator($value);
							}
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
			if ($sub === 'read-only') {
				throw new PropertyIsReadOnly(
					'Property '.$name.' of "'.$sub.'" access'
				);
			} else if ($sub === 'write-only') {
				throw new PropertyIsWriteOnly(
					'Property '.$name.' of "'.$sub.'" access'
				);
			}

		}

		throw new PropertyDoesNotExist('No such property '.$name);
	}

	private function _simpUtilsGetValidator($item, $attr, $call_type): ?Closure {
		$validators_enabled = CommonMemoryCacheIndex::$property_validators_enabled;
		$validators = CommonMemoryCacheIndex::$property_validators;

		if ($validators_enabled && $call_type === Property::TYPE_SET) {
			$attr_instance = $attr->newInstance();
			$valid = $attr_instance?->valid ?? false;

			if ($valid === true &&
				$validators_enabled === CommonMemoryCacheIndex::PROPERTY_VALIDATOR_ENABLED
			) {
				if ($item instanceof ReflectionProperty) {
					$t = $item?->getType();
				} else if ($item instanceof ReflectionMethod) {
					$param = $item->getParameters()[0] ?? null;
					/** @var \ReflectionParameter $param */
					$t = $param?->getType();
				}
				if ($t instanceof ReflectionUnionType) {
					// NOTE Union-Types are not supported due to unpredictable nature
					return null; // @codeCoverageIgnore
				} else if ($t instanceof ReflectionNamedType) {
					$class = $t->getName();

					if ($class === 'mixed') {
						return null;
					}

					if (empty($validators[$class])) {
						$class = PHP::classShortName($class);
						if (empty($validators[$class])) {
							return null; // @codeCoverageIgnore
						}
					}
					if (!empty($validators[$class]) && PHP::isClass($validators[$class])) {
						$closure = Closure::fromCallable([$validators[$class], 'process']);
						return $closure;
					}
				}
			} else if (is_string($valid) &&
				$validators_enabled >= CommonMemoryCacheIndex::PROPERTY_VALIDATOR_LIMITED
			) {
				if (!empty($validators[$valid])) {
					$closure = Closure::fromCallable([$validators[$valid], 'process']);
					return $closure;
				} else if (PHP::isClass($valid)) {
					$closure = Closure::fromCallable([$valid, 'process']);
					return $closure;
				}
			}
		}

		return null;
	}

	private function _simpUtilsClassLevelHideUp($extract_attr_on, $debug_hide_attr_on): ?Box {
		$this_obj_reflection = new ReflectionObject($this);
		foreach ($this_obj_reflection->getAttributes() as $attr) {
			$attr_instance = $attr->newInstance();
			$is_applicable = $attr_instance instanceof BasicOutputControlAttribute
				&& $attr_instance->isApplicable($extract_attr_on, $debug_hide_attr_on);

			if ($is_applicable) {
				/** @var \spaf\simputils\generic\BasicOutputControlAttribute $attr_instance */
				$res = $attr_instance->appliedOnClass($this);
				if (!is_null($res)) {
					return $res;
				}
			}
		}

		return null;
	}

	/**
	 * @param bool $extract_attr_on    Whether the Extract attribute behaviour should be applied
	 * @param bool $debug_hide_attr_on Whether the DebugHide attribute behaviour should be applied
	 *
	 * @return array|string[]
	 */
	protected function ___extractFields(
		bool $extract_attr_on = true,
		bool $debug_hide_attr_on = false
	) {
		$hide_up = $this->_simpUtilsClassLevelHideUp($extract_attr_on, $debug_hide_attr_on);
		if (!is_null($hide_up)) {
			return (array) $hide_up;
		}

		$batch_array_of_prop_types = [PropertyBatch::TYPE_GET, PropertyBatch::TYPE_BOTH];
		$property_array_of_prop_types = [Property::TYPE_GET, Property::TYPE_BOTH];

		$res = PHP::box();

		foreach ($this->getAllTheLastMethodsAndProperties() as $item) {
			/** @var ReflectionMethod|ReflectionProperty $item */
			$name = $item->getName();

			$prefix = null;
			$ta = null; // target attribute

			if ($item->isStatic()) {
				// TODO Implement options in InitConfig
				$prefix = 'static::';
				continue;
			}

			$item_attributes = $item->getAttributes();

			if (empty($item_attributes) && $item instanceof ReflectionMethod) {
				// NOTE Methods without attributes are absolutely irrelevant at here,
				//      so skipping
				continue;
			}

			$it_set_by_attr = false;
			$it_attr_value = null;

			foreach ($item_attributes as $attr) {
				if (in_array($attr->getName(), [Property::class, PropertyBatch::class])) {
					if (empty($ta)) {
						$ta = $attr;
					}
					continue;
				}
				$attr_instance = $attr->newInstance();
				$is_applicable = $attr_instance instanceof BasicOutputControlAttribute
					&& $attr_instance->isApplicable($extract_attr_on, $debug_hide_attr_on);

				if ($is_applicable) {
					/** @var BasicOutputControlAttribute $attr_instance */
					$sub_res = $attr_instance->appliedOnProperty();
					if ($sub_res === false) {
						// NOTE Any of the attribute decided to hide up the field
						continue 2;
					} else if ($sub_res === true) {
						// NOTE Skipping those that do not affect the result
					} else {
						$it_attr_value = $sub_res;
						$it_set_by_attr = true;
					}
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
							$res["{$expected_name}"] = $it_set_by_attr
								?$it_attr_value:$this->$expected_name;
						}
					}
				} else if (!empty($ta) && $ta->getName() === Property::class) {

					$expected_name = Property::expectedName(
						$item->getName(),
						$ta,
						$ta->getArguments(),
						$item
					);
					$method_type = Property::methodAccessType($item, $ta);

					if (in_array($method_type, $property_array_of_prop_types)) {
						$res["{$expected_name}"] = $it_set_by_attr
							?$it_attr_value:$this->$expected_name;
					}
				} else {
					// NOTE Real PHP native property
					$item->setAccessible(true);
					$res["{$prefix}{$name}"] = $it_set_by_attr
						?$it_attr_value:$item->getValue($this);
					$item->setAccessible(false);
				}
			} else if ($item instanceof ReflectionMethod) {
				// NOTE Property/PropertyBatch from method

				if (!empty($ta) && $ta->getName() === Property::class) {
					$expected_name = Property::expectedName($item, $ta);
					$method_type = Property::methodAccessType($item, $ta);

					if (in_array($method_type, $property_array_of_prop_types)) {
						$res["{$expected_name}"] = $it_set_by_attr
							?$it_attr_value:$this->$expected_name;
					}
				} else if (!empty($ta) && $ta->getName() === PropertyBatch::class) {
					[$expected_names, $_] = PropertyBatch::expectedNamesAndDefaultValues(
						$this, $item, $ta
					);
					$access_type = PropertyBatch::accessType($ta);

					if (in_array($access_type, $batch_array_of_prop_types)) {

						foreach ($expected_names as $expected_name) {
							$res["{$expected_name}"] = $it_set_by_attr
								?$it_attr_value:$this->$expected_name;
						}
					}
				}
			}
		}
		$res->sort(by_values: false);
		return (array) $res;
	}

	/**
	 * @param mixed  $value Supplied value
	 * @param string $type  Call type ('set' or 'get')
	 * @param string $name  Property name that has been requested
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	private function _simpUtilsPropertyFieldMethodSet(mixed $value, string $type, string $name) {
		$this->$name = $value;
	}

	/**
	 * @param mixed  $value Supplied value
	 * @param string $type  Call type ('set' or 'get')
	 * @param string $name  Property name that has been requested
	 *
	 * @codeCoverageIgnore
	 * @return mixed
	 */
	private function _simpUtilsPropertyFieldMethodGet(mixed $value, string $type, string $name) {
		return $this->$name;
	}

	/**
	 * @return array
	 */
	public function __debugInfo() {
		return (array) PHP::metaMagicSpell($this, 'extractFields', false, true);
	}
}
