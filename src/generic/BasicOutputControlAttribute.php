<?php

namespace spaf\simputils\generic;

use Attribute;
use spaf\simputils\models\Box;

/**
 * Basic Attribute Class that controls output for Properties and __debugInfo
 *
 * @see \spaf\simputils\traits\PropertiesTrait::___extractFields()
 */
#[Attribute]
abstract class BasicOutputControlAttribute extends BasicAttribute {

	/**
	 * Controls content output of the class
	 *
	 * @return ?Box Null is returned in case attribute does not want to modify the target
	 *              object. If Box returned - then this box will have to be used
	 *              as the resulting __debugInfo/array
	 */
	abstract function appliedOnClass(): ?Box;

	/**
	 * Controls content output of the property
	 *
	 * @param null|mixed $value Containing the final value/object/etc.
	 *                          that suppose to be returned.
	 *
	 * @return null|string|bool
	 */
	abstract function appliedOnProperty(mixed $value = null): null|string|bool;

	/**
	 * Checking the settings
	 *
	 * @param bool $extract_attr_on    Extract attribute on
	 * @param bool $debug_hide_attr_on DebugHide attribute on
	 *
	 * @return bool
	 */
	abstract function isApplicable(bool $extract_attr_on, bool $debug_hide_attr_on): bool;
}
