<?php

namespace spaf\simputils\attributes;

use Attribute;
use spaf\simputils\generic\BasicOutputControlAttribute;
use spaf\simputils\models\Box;
use spaf\simputils\models\Secret;
use spaf\simputils\PHP;

/**
 * Hide/Hide value of fields/properties for "DebugOutput"s like `pr`, `pd` or `print_r`
 *
 * Variant 1:
 *  **Important:** If applied to the whole class - class will be silenced! So not a single
 *  field would be displayed!
 *
 * Variant 2:
 *  If used for the class variable/field - it will be ignored during DebugOutput
 *
 * Variant 3:
 *  If applied for the method without "Property" or "PropertyBatch" - this attribute is ignored,
 *  if applied to the "Property" or "PropertyBatch" methods - their properties will be ignored
 *  during DebugOutput.
 *
 * **Important:** Difference between "Hiding" and "Non-Processing":
 *  * Hide - will remove any output regarding the field (there will be no indication
 *    of a field at all!!!)
 *  * Hide value - will be still listed, but will not show value (and in case of "Property"
 *    and "PropertyBatch" will not run those methods, so no impact on the system)
 *
 * NOTE This attribute could be used to hide `password` or `secrete` values for output
 *      or even logging!
 *
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class DebugHide extends BasicOutputControlAttribute {

	static $default_placeholder = '****';

	public function __construct(
		public bool $hide_all = true,
		public ?string $show_instead = '****',
	) { }

	/**
	 * @inheritDoc
	 */
	function appliedOnClass(): ?Box {
		if ($this->hide_all) {
			return PHP::box();
		}
		return PHP::box([$this->show_instead ?? static::$default_placeholder]);
	}

	/**
	 * @inheritDoc
	 */
	function appliedOnProperty(mixed $value = null): null|string|false {

		if ($value instanceof Secret) {
			$stringified = "{$value}";
			if ($stringified !== $value->value) {
				return $stringified;
			}
		}


		return $this->hide_all
			?false
			:$this->show_instead;
	}

	/**
	 * @inheritDoc
	 */
	function isApplicable(bool $extract_attr_on, bool $debug_hide_attr_on): bool {
		return $debug_hide_attr_on;
	}
}
