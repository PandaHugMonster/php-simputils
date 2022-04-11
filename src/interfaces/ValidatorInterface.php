<?php

namespace spaf\simputils\interfaces;

/**
 * Validator interface
 *
 *
 * Really important to highlight that "Validator" does not just validate, but normalizes
 * and converts when applicable
 */
interface ValidatorInterface {

	/**
	 * Validate/Normalizes/Converts value from "setter"
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public static function process(mixed $value): mixed;
}
