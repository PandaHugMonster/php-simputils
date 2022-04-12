<?php

namespace spaf\simputils\traits;

use spaf\simputils\exceptions\ReadOnlyProblem;

/**
 * ArrayAccess additional trait for read only functionality
 *
 * It's better to use it together with those classes that implementing ArrayAccess interface,
 * and you want it to be just a ReadOnly array.
 *
 * Example:
 * ```php
 *  use ArrayAccess;
 *  use spaf\simputils\traits\ArrayAccessReadOnlyTrait;
 *
 *  class MyArrayObj implements ArrayAccess {
 *      use ArrayAccessReadOnlyTrait;
 *
 *      public function offsetExists(mixed $offset): bool {
 *          // your checking existence here
 *      }
 *      public function offsetGet(mixed $offset): mixed {
 *          // your returning content of the item here
 *      }
 *
 *  }
 *
 * ```
 *
 * @see \ArrayAccess
 * @codeCoverageIgnore
 */
trait ArrayReadOnlyAccessTrait {

	/**
	 * @var bool
	 */
	private bool $_simp_utils_read_only = true;

	/**
	 * Sets the read-only flag
	 *
	 * TODO Has to be refactored/or reorganized at some point
	 * FIX  Temporarily set as public instead of protected. Fix it at some point!
	 * @param bool $val Enable or Disable, default is to enable
	 *
	 * @return void
	 */
	public function _simpUtilsSetReadOnly(bool $val = true) {
		$this->_simp_utils_read_only = $val;
	}

	/**
	 * Checks read-only flag
	 *
	 * FIX  Temporarily set as public instead of protected. Fix it at some point!
	 * TODO Has to be refactored/or reorganized at some point
	 * @return bool
	 */
	public function _simpUtilsIsReadOnly(): bool {
		return $this->_simp_utils_read_only;
	}

	/**
	 * Default ArrayAccess setting method
	 *
	 * @param mixed $offset An offset
	 * @param mixed $value  A value
	 *
	 * @return void
	 */
	final public function offsetSet(mixed $offset, mixed $value): void {
		if ($this->_simpUtilsIsReadOnly()) {
			$this->cannotUseIt();
		} else {
			parent::offsetSet($offset, $value);
		}
	}

	/**
	 * @param mixed $offset Offset
	 *
	 * @return void
	 */
	public function offsetUnset(mixed $offset): void {
		if ($this->_simpUtilsIsReadOnly()) {
			$this->cannotUseIt();
		} else {
			parent::offsetUnset($offset);
		}
	}

	/**
	 * @return void
	 */
	private function cannotUseIt(): void {
		throw new ReadOnlyProblem('Modification (setting) of this object/array is not allowed');
	}
}
