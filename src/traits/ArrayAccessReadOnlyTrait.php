<?php

namespace spaf\simputils\traits;

use Exception;

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
 */
trait ArrayAccessReadOnlyTrait {

	/**
	 * Default ArrayAccess setting method
	 *
	 * @param mixed $offset An offset
	 * @param mixed $value  A value
	 *
	 * @return void
	 * @throws \Exception It's not allowed to change the value of read-only object
	 */
	final public function offsetSet(mixed $offset, mixed $value): void {
		$this->cannotUseIt();
	}

	/**
	 * @param mixed $offset Offset
	 *
	 * @return void
	 * @throws \Exception Modification of the object through the array interface is not allowed
	 */
	public function offsetUnset(mixed $offset): void {
		$this->cannotUseIt();
	}

	/**
	 * @return void
	 * @throws \Exception Modification of the object through the array interface is not allowed
	 */
	private function cannotUseIt(): void {
		throw new Exception('Modification (setting) of this object/array is not allowed');
	}
}
