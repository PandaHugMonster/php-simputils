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
 *      function offsetExists(mixed $offset): bool {
 *          // your checking existence here
 *      }
 *      function offsetGet(mixed $offset): mixed {
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
	 * TODO Temporarily set as public instead of protected. Fix it at some point!
	 * @param bool $val Enable or Disable, default is to enable
	 *
	 * @return void
	 */
	function _simpUtilsSetReadOnly(bool $val = true) {
		$this->_simp_utils_read_only = $val;
	}

	/**
	 * Checks read-only flag
	 *
	 * TODO Temporarily set as public instead of protected. Fix it at some point!
	 * TODO Has to be refactored/or reorganized at some point
	 * @return bool
	 */
	function _simpUtilsIsReadOnly(): bool {
		return $this->_simp_utils_read_only;
	}

	/**
	 * Default ArrayAccess setting method
	 *
	 * @param mixed $offset An offset
	 * @param mixed $value  A value
	 *
	 * @return void
	 * @throws ReadOnlyProblem
	 */
	final function offsetSet(mixed $offset, mixed $value): void {
		$this->set($offset, $value, false);
	}

	/**
	 * @param mixed $offset Offset
	 *
	 * @return void
	 */
	function offsetUnset(mixed $offset): void {
		$this->unset($offset, false);
	}

	/**
	 * Internal Setting Method
	 *
	 * It allows you to set key value pairs internally inside of your object,
	 * but do not let to do that from outside of your object
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param bool  $is_internal
	 *
	 * @return void
	 * @throws ReadOnlyProblem
	 */
	protected function set(mixed $key, mixed $value, bool $is_internal = false): void {
		if (!$is_internal && $this->_simpUtilsIsReadOnly()) {
			$this->cannotUseIt();
		} else {
			parent::offsetSet($key, $value);
		}
	}

	/**
	 * Internal Un-setting Method
	 *
	 * It allows you to unset key value pairs internally inside of your object,
	 * but do not let to do that from outside of your object
	 *
	 * @param mixed $key
	 * @param bool  $is_internal
	 *
	 * @return void
	 * @throws ReadOnlyProblem
	 */
	protected function unset(mixed $key, bool $is_internal = false): void {
		if (!$is_internal && $this->_simpUtilsIsReadOnly()) {
			$this->cannotUseIt();
		} else {
			parent::offsetUnset($key);
		}
	}

	/**
	 * @return void
	 * @throws ReadOnlyProblem
	 */
	protected function cannotUseIt(): void {
		throw new ReadOnlyProblem('Modification (setting/unsetting) of this object/array is not allowed');
	}
}
