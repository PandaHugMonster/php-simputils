<?php

namespace spaf\simputils\traits;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use spaf\simputils\attributes\Property;
use spaf\simputils\attributes\PropertyBatch;
use spaf\simputils\exceptions\PropertyAccessError;
use spaf\simputils\exceptions\PropertyDoesNotExist;
use spaf\simputils\special\PropertiesCacheIndex;
use function in_array;
use function is_numeric;
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
 * FIX  To drastically optimize
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

	public function __isset($name): bool {
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
		if (!empty($value_store_ref = $settings['storage'])) {
			if ($value_store_ref === PropertyBatch::STORAGE_SELF) {
				$value_store = &$this;
			} else {
				$value_store = &$this->$value_store_ref;
			}
			return $value_store[$name] ?? null;
		}
		return null;
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
				$expected_names = $args[2] ?? $args['names'] ?? null;

				if (empty($expected_names)) {
					$item->setAccessible(true);
					$_group = [];
					$expected_names = [];
					$default_values = [];
					if ($item instanceof ReflectionMethod) {
						$_group = (array) $item->invoke($this);
					} else if ($item instanceof ReflectionProperty) {
						$_group = (array) $item->getValue($this);
					}
					$item->setAccessible(false);
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
				if (in_array($name, $expected_names)) {
					$method_name = '____propertyBatchMethod';

					$access_type = $args[0] ?? $args['type'] ?? PropertyBatch::TYPE_BOTH;
					$value_store_ref = $args[3] ?? $args['storage'] ?? '____property_batch_storage';

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

							if (!isset($index[$key_type = $key.'#'.$access_type])) {
								$index[$key_type] = $method_name.ucfirst($access_type);
							} else {
								$index[$key.'#'.$access_type] = $method_name.ucfirst($access_type);
							}
						}

						if ($access_type === PropertyBatch::TYPE_GET) {
							if ($check_and_do_not_call) {
								return true;
							}
							return $this->$method_name(null, $call_type, $name);

						} else if ($access_type === PropertyBatch::TYPE_SET) {
							return $this->$method_name($value, $call_type, $name);

						}

					} elseif (empty($sub)) {
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
						$args = $attr->getArguments();

						$expected_name = $args[0] ?? $args['name'] ?? $item->name;
						if ($name === $expected_name) {
							$method_type = $args[1] ?? $args['type'] ?? null;

							if (!empty($method_type)) {
								$method_type = strtolower($method_type);
							} else {
								$return_type = $item?->getReturnType()?->getName() ?? 'mixed';

								$is_setter = (bool) $item->getNumberOfParameters();
								$is_getter = $return_type !== 'void' && $return_type !== 'never';

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
							} elseif (empty($sub)) {
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
}
