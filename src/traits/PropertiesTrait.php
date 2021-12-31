<?php

namespace spaf\simputils\traits;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use ReflectionUnionType;
use spaf\simputils\attributes\Property;
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\exceptions\PropertyAccessError;
use spaf\simputils\exceptions\PropertyDoesNotExist;
use spaf\simputils\special\PropertiesCacheIndex;
use function implode;
use function in_array;
use function is_null;
use function is_numeric;
use function method_exists;
use function strtolower;
use function ucfirst;

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
 * __Afternote:__ Basically it's safe enough for performance to use `Properties`, though if you have
 * extremely complex and big monolith code (which is not a good thing in the most cases),
 * you might have some dropdowns of efficiency if compared to direct calls, but in the most cases
 * it will be so negligible, that almost always it would be much more efficient to fix/optimize
 * the "complexities" of your own solution/code first.
 *
 * TODO Implement normal PropertyReflection class!
 *
 * FIX  !!! Absolute MESS! Fully refactor and improve, but do not damage functionality
 */
trait PropertiesTrait {

	private $____property_batch_storage = [];

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
	 * @param $ref_name
	 * @param $ref_name_type
	 * @param $name
	 * @param $call_type
	 * @param null $value
	 * @param bool $check_and_do_not_call
	 *
	 * @return bool|void
	 * @throws \ReflectionException
	 * @throws \spaf\simputils\exceptions\PropertyAccessError
	 * @throws \spaf\simputils\exceptions\PropertyDoesNotExist
	 */
	private function ____prepareProperty(
		$name, $call_type, $value = null, $check_and_do_not_call = false
	) {
		$ref_name = static::class.'#'.$name;
		$ref_name_type = $ref_name.'#'.Property::TYPE_GET;

		####
		if (!isset(PropertiesCacheIndex::$index[$ref_name_type])) {
			PropertiesCacheIndex::$index[$ref_name_type] = null;
		}
		$index = &PropertiesCacheIndex::$index;
		####

		$sub = null;

		$ref = new ReflectionClass($this);
		$class_items = array_merge($ref->getMethods(), $ref->getProperties());

		foreach ($class_items as $item) {
			// NOTE PropertyBatch part
			if (!empty($attr = $item->getAttributes(PropertyBatch::class)[0] ?? null)) {
				$args = $attr->getArguments();
				$expected_names = $this->_propertyBatchExpectedNames($item, $attr);

				if (in_array($name, $expected_names)) {
					$method_name = '____propertyBatchMethod';

					$access_type = $this->_propertyBatchAccessType($item, $attr);
					$value_store_ref = $args[2] ?? $args['storage'] ?? '____property_batch_storage';
					if ($value_store_ref === PropertyBatch::STORAGE_SELF) {
						$value_store = &$this;
					} else {
						$value_store = &$this->$value_store_ref;
					}
					if (!empty($default_values)) {
						if (
							$value_store_ref === PropertyBatch::STORAGE_SELF
							&& method_exists($this, '____setReadOnly')
						) {
							// FIX Fix those at some point
							$this->____setReadOnly(false);
						}
						foreach ($default_values as $k => $v) {
							$value_store[$k] = $v;
						}
						if (
							$value_store_ref === PropertyBatch::STORAGE_SELF
							&& method_exists($this, '____setReadOnly')
						) {
							// FIX Fix those at some point
							$this->____setReadOnly(true);
						}
					}

					if ($access_type === PropertyBatch::TYPE_BOTH) {
						foreach ($expected_names as $exp_name) {
							$key = static::class.'#'.$exp_name;
							PropertiesCacheIndex::$property_settings[$key]['storage']
								= $value_store_ref;
							if (!isset($index[$key_type = $key.'#'.PropertyBatch::TYPE_GET])) {
								$index[$key_type] = $method_name.'Get';
							} else if (
								!isset($index[$key_type = $key.'#'.PropertyBatch::TYPE_SET])
							) {
								$index[$key_type] = $method_name.'Set';
							}
						}

						if ($check_and_do_not_call) {
							return true;
						}
						return $this->{$method_name.ucfirst($call_type)}(
							$value, $call_type, $name
						);

					} else if ($access_type === $call_type) {

						foreach ($expected_names as $exp_name) {
							$key = static::class.'#'.$exp_name;
							PropertiesCacheIndex::$property_settings[$key]['storage']
								= $value_store_ref;

							$key_type = $key.'#'.$access_type;
							$index[$key_type] = $method_name.ucfirst($access_type);
						}

						if ($access_type === PropertyBatch::TYPE_GET) {
							if ($check_and_do_not_call) {
								return true;
							}
							return $this->{$method_name.ucfirst($access_type)}(
								null, $call_type, $name
							);

						} else if ($access_type === PropertyBatch::TYPE_SET) {
							return $this->{$method_name.ucfirst($access_type)}(
								$value, $call_type, $name
							);

						}

					} else if (empty($sub)) {
						$sub = $access_type === Property::TYPE_GET?'read-only':'write-only';
					}

					if ($check_and_do_not_call) {
						return false;
					}
				}

			// NOTE Property part
			} else {
				if (!empty($attr = $item->getAttributes(Property::class)[0] ?? null)) {
					if (!empty($attr)) {
						/** @var \ReflectionAttribute $attr */

						if ($name === $this->_propertyExpectedName($item, $attr)) {
							$method_type = $this->_propertyMethodAccessType($item, $attr);

							if ($method_type === Property::TYPE_BOTH) {
								if (!isset($index[$key_type = $ref_name.'#'.Property::TYPE_GET])) {
									$index[$key_type] = $item->name;
								}
								if (!isset($index[$key_type = $ref_name.'#'.Property::TYPE_SET])) {
									$index[$key_type] = $item->name;
								}

								if ($check_and_do_not_call) {
									return true;
								}
								return $this->{$item->name}(
									$value, $call_type, $name
								);
							} else if ($call_type === $method_type) {
								if ($method_type === Property::TYPE_GET) {
									if (
										!isset($index[$key_type = $ref_name.'#'.Property::TYPE_GET])
									) {
										$index[$key_type] = $item->name;
									}

									if ($check_and_do_not_call) {
										return true;
									}
									return $this->{$item->name}(
										null, $call_type, $name
									);
								} else if ($method_type === Property::TYPE_SET) {
									if (
										!isset($index[$key_type = $ref_name.'#'.Property::TYPE_SET])
									) {
										$index[$key_type] = $item->name;
									}
									return $this->{$item->name}(
										$value, $call_type, $name
									);
								}
							}
							if (empty($sub)) {
								$sub = $method_type === Property::TYPE_GET?'read-only':'write-only';
							}

							if ($check_and_do_not_call) {
								return false;
							}
						}
					}
				}
			}

		}
		if ($check_and_do_not_call) {
			return false;
		}
		if (!empty($sub)) {
			throw new PropertyAccessError(
				'Property '.$name.' of "'.$sub.'" access'
			);
		}

		throw new PropertyDoesNotExist('No such property '.$name);
	}

	private function _propertyBatchExpectedNames($ref, \ReflectionAttribute $attr): ?array {
		$args = $attr->getArguments();

		$expected_names = $args[3] ?? null;

		if (empty($expected_names)) {
			$ref->setAccessible(true);
			$_group = [];
			$expected_names = [];
			$default_values = [];
			if ($ref instanceof ReflectionMethod) {
				$_group = (array) $ref->invoke($this);
			} else if ($ref instanceof ReflectionProperty) {
				$_group = (array) $ref->getValue($this);
			}
			$ref->setAccessible(false);
			if (!empty($_group)) {
				foreach ($_group as $k => $v) {
					if (is_numeric($k)) {
						$expected_names[] = $v;
					} else {
						$default_values[$k] = $v;
						$expected_names[] = $k;
					}

				}
			}
		}

		return $expected_names;
	}

	private function _propertyExpectedName($ref, \ReflectionAttribute $attr) {
		$args = $attr->getArguments();
		return $args[0] ?? $args['name'] ?? $ref->name;
	}

	private function _propertyBatchAccessType($ref, \ReflectionAttribute $attr) {
		$args = $attr->getArguments();
		return $args[0] ?? $args['type'] ?? PropertyBatch::TYPE_BOTH;
	}

	private function _propertyMethodAccessType($ref, \ReflectionAttribute $attr) {
		$args = $attr->getArguments();

		$method_type = $args[1] ?? $args['type'] ?? null;

		if (!empty($method_type)) {
			$method_type = strtolower($method_type);
		} else {
			$ref_ret_type = $ref?->getReturnType() ?? null;
			if ($ref_ret_type instanceof ReflectionUnionType) {
				$r = [];
				foreach ($ref_ret_type->getTypes() as $ref_ret_type_item) {
					$r[] = $ref_ret_type_item;
				}
				$return_type = implode('|', $r);
			} else {
				$return_type = $ref_ret_type?->getName() ?? null;
			}

			$is_setter = (bool) $ref->getNumberOfParameters()
				|| $return_type === 'void'
				|| $return_type === 'never';
			$is_getter = !$is_setter || ($is_setter
					&& !is_null($return_type)
					&& $return_type !== 'void'
					&& $return_type !== 'never');

			if ($is_setter && $is_getter) {
				// BOTH
				$method_type = Property::TYPE_BOTH;
			} else if ($is_getter) {
				// GET
				$method_type = Property::TYPE_GET;
			} else if ($is_setter) {
				// SET
				$method_type = Property::TYPE_SET;
			}
		}

		return $method_type;
	}

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
				$expected_name = $this->_propertyExpectedName($item, $attr);
				$method_type = $this->_propertyMethodAccessType($item, $attr);

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
				$expected_names = $this->_propertyBatchExpectedNames($item, $attr);
				$access_type = $this->_propertyBatchAccessType($item, $attr);

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
