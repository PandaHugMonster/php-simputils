<?php

namespace spaf\simputils\traits;

use ReflectionClass;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\PropertyAccessError;
use spaf\simputils\exceptions\PropertyDoesNotExist;
use spaf\simputils\special\PropertiesCacheIndex;
use function strtolower;

/**
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
 */
trait PropertiesTrait {

	/**
	 * @param string $name
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get($name): mixed {
		####
		if (!isset(PropertiesCacheIndex::$index[static::class])) {
			PropertiesCacheIndex::$index[static::class] = [];
		}
		$store = &PropertiesCacheIndex::$index[static::class];
		####
		if (!empty($store[$name]) && !empty($store[$name][Property::TYPE_GET])) {
			$method_name = $store[$name][Property::TYPE_GET];
			return $this->$method_name(null, Property::TYPE_GET);
		}
		return $this->____prepareProperty($name, Property::TYPE_GET);
	}

	public function __set($name, $value): void {
		####
		if (!isset(PropertiesCacheIndex::$index[static::class])) {
			PropertiesCacheIndex::$index[static::class] = [];
		}
		$store = &PropertiesCacheIndex::$index[static::class];
		####
		if (!empty($store[$name]) && !empty($store[$name][Property::TYPE_SET])) {
			$method_name = $store[$name][Property::TYPE_SET];
			$this->$method_name($value, Property::TYPE_SET);
		} else {
			$this->____prepareProperty($name, Property::TYPE_SET, $value);
		}
	}

	public function __isset($name): bool {
		####
		if (!isset(PropertiesCacheIndex::$index[static::class])) {
			PropertiesCacheIndex::$index[static::class] = [];
		}
		$store = &PropertiesCacheIndex::$index[static::class];
		####
		if (!empty($store[$name])&& !empty($store[$name][Property::TYPE_GET])) {
			return true;
		}
		return $this->____prepareProperty(
			$name, Property::TYPE_GET,
			check_and_do_not_call: true
		);
	}

	/**
	 * @param $name
	 * @param $call_type
	 * @param null $value
	 * @param bool $check_and_do_not_call
	 *
	 * @return bool|void
	 * @throws \spaf\simputils\exceptions\PropertyAccessError
	 * @throws \spaf\simputils\exceptions\PropertyDoesNotExist
	 */
	private function ____prepareProperty(
		$name, $call_type, $value = null, $check_and_do_not_call = false
	) {
		####
		if (!isset(PropertiesCacheIndex::$index[static::class])) {
			PropertiesCacheIndex::$index[static::class] = [];
		}
		$store = &PropertiesCacheIndex::$index[static::class];
		####

		$sub = null;

		$ref = new ReflectionClass($this);
		foreach ($ref->getMethods() as $method) {

			$attrs_list = $method->getAttributes(Property::class);
			$attr_ref = $attrs_list[0] ?? null;
			if (!empty($attr_ref)) {
				/** @var Property $attr */
				$args = $attr_ref->getArguments();

				$expected_name = $args[0] ?? $args['name'] ?? $method->name;
				if ($name === $expected_name) {
					$method_type = $args[1] ?? $args['type'] ?? null;

					if (!empty($method_type)) {
						$method_type = strtolower($method_type);
					} else {
						$return_type = $method?->getReturnType()?->getName() ?? 'mixed';

						$method_type = $this->type ?? null;
						if (empty($method_type)) {

							$is_setter = (bool) $method->getNumberOfParameters();
							$is_getter = (!$is_setter && !empty($return_type))
								&& $return_type !== 'void'
								&& $return_type !== 'never';

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
					}

					if ($method_type === Property::TYPE_BOTH) {
						if (!isset($store[$expected_name])) {
							$store[$expected_name] = [
								Property::TYPE_GET => $method->name,
								Property::TYPE_SET => $method->name,
							];
						} else {
							$store[$expected_name][Property::TYPE_GET]
								= $store[$expected_name][Property::TYPE_SET]
									= $method->name;
						}

						if ($check_and_do_not_call) {
							return true;
						}
						return $this->{$method->name}($value, $call_type);
					} else if ($call_type === $method_type) {
						if ($method_type === Property::TYPE_GET) {
							if (!isset($store[$expected_name])) {
								$store[$expected_name] = [
									Property::TYPE_GET => $method->name,
								];
							} else {
								$store[$expected_name][Property::TYPE_GET] = $method->name;
							}

							if ($check_and_do_not_call) {
								return true;
							}
							return $this->{$method->name}(null, $call_type);
						} else if ($method_type === Property::TYPE_SET) {
							if (!isset($store[$expected_name])) {
								$store[$expected_name] = [
									Property::TYPE_SET => $method->name,
								];
							} else {
								$store[$expected_name][Property::TYPE_SET] = $method->name;
							}
							return $this->{$method->name}($value, $call_type);
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
