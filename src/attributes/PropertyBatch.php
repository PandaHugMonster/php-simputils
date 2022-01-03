<?php

namespace spaf\simputils\attributes;

use Attribute;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionProperty;
use spaf\simputils\special\PropertiesCacheIndex;

/**
 * @package spaf\simputils\attributes
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class PropertyBatch extends Property {

	/**
	 * This is relevant only for ArrayObject similar objects,
	 * then the values will be stored inside of the object (like ArrayObject enables)
	 */
	const STORAGE_SELF = '#SELF';

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(
		public ?string $type = null,
		public ?string $modifier = null,
		public ?string $storage = null,
		public ?array $names = null,
	) {}

	public static function valueStoreRef(
		$obj,
		$attr,
		$default_values = null,
		$args = null
	): string {

		$args = $args ?? $attr->getArguments();

		$value_store_ref = $args[2] ?? $args['storage'] ?? '____property_batch_storage';
		if (!empty($default_values)) {
			if ($value_store_ref === PropertyBatch::STORAGE_SELF) {
				$value_store = &$obj;
			} else {
				$value_store = &$obj->$value_store_ref;
			}

			$is_read_only = is_object($value_store) &&
				method_exists($value_store, '____isReadOnly') &&
				$value_store->____isReadOnly();

			if ($is_read_only) {
				$value_store->____setReadOnly(false);
			}
			foreach ($default_values as $k => $v) {
				$value_store[$k] = $v;
			}
			if ($is_read_only) {
				$value_store->____setReadOnly(true);
			}
		}

		return $value_store_ref;
	}

//	public static function prepareAllExpectedNames($obj, $expected_names, $value_store_ref) {
//		foreach ($expected_names as $exp_name) {
//			$key = $obj::class.'#'.$exp_name;
//			PropertiesCacheIndex::$property_settings[$key]['storage'] = $value_store_ref;
//
//			if (!isset($index[$key_type = $key.'#'.PropertyBatch::TYPE_GET])) {
//				$index[$key_type] = $func_ref_prefix.'Get';
//			} else if (
//				!isset($index[$key_type = $key.'#'.PropertyBatch::TYPE_SET])
//			) {
//				$index[$key_type] = $func_ref_prefix.'Set';
//			}
//		}
//	}

	/**
	 * @param $obj
	 * @param $item
	 * @param $attr
	 * @param $name
	 * @param $value
	 * @param $call_type
	 *
	 * FIX  Subject to a better optimization later
	 *
	 * @return array|string[]
	 */
	public static function subProcess(
		$obj,
		$item,
		$attr,
		$name,
		$call_type
	): array {
		$ref_name = $obj::class.'#'.$name;
		$ref_name_type = $ref_name.'#'.static::TYPE_GET;

		$func_ref_prefix = '____propertyBatchMethod';
		$func_ref = $func_ref_prefix.ucfirst($call_type);

		//// NOTE   Impossible to optimize and extract due to PHP limitations on passing by ref...
		if (!isset(PropertiesCacheIndex::$index[$ref_name_type])) {
			PropertiesCacheIndex::$index[$ref_name_type] = null;
		}
		$index = &PropertiesCacheIndex::$index;
		$prop_settings_index = &PropertiesCacheIndex::$property_settings;
		////

		$args = $attr->getArguments();

		[$expected_names, $default_values]
			= static::expectedNamesAndDefaultValues($obj, $item, $attr);

		if (in_array($name, $expected_names)) {

			$access_type = static::accessType($attr);
			$value_store_ref = static::valueStoreRef($obj, $attr, $default_values, $args);

			if ($access_type === PropertyBatch::TYPE_BOTH) {
				foreach ($expected_names as $exp_name) {
					$key = $obj::class.'#'.$exp_name;
					$prop_settings_index[$key]['storage'] = $value_store_ref;

					$index[$key.'#'.PropertyBatch::TYPE_GET] = $func_ref_prefix.'Get';
					$index[$key.'#'.PropertyBatch::TYPE_SET] = $func_ref_prefix.'Set';
				}
				return [$func_ref, true];
			} else if ($access_type === $call_type) {

				foreach ($expected_names as $exp_name) {
					$key = $obj::class.'#'.$exp_name;
					$prop_settings_index[$key]['storage'] = $value_store_ref;

					$index[$key.'#'.$call_type] = $func_ref;
				}

				return [$func_ref, true];
			}

			return [$func_ref, $access_type === static::TYPE_GET?'read-only':'write-only'];
		}

		return [$func_ref, false];
	}

	public static function expectedNamesAndDefaultValues(
		$obj,
		$ref,
		ReflectionAttribute $attr
	): array {
		$args = $attr->getArguments();

		$expected_names = $args[3] ?? null;

		if (empty($expected_names)) {
			$ref->setAccessible(true);
			$_group = [];
			$expected_names = [];
			$default_values = [];
			if ($ref instanceof ReflectionMethod) {
				$_group = (array) $ref->invoke($obj);
			} else if ($ref instanceof ReflectionProperty) {
				$_group = (array) $ref->getValue($obj);
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

		return [$expected_names, $default_values];
	}

	public static function accessType(ReflectionAttribute $attr) {
		$args = $attr->getArguments();
		return $args[0] ?? $args['type'] ?? PropertyBatch::TYPE_BOTH;
	}
}
