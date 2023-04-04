<?php

namespace spaf\simputils\models;

use Generator;
use spaf\simputils\components\init\AppInitConfig;
use spaf\simputils\exceptions\SortingIsNotPermitted;
use spaf\simputils\Math;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 * Stack LIFO
 *
 *  Last IN - First OUT
 * |===================<>
 *
 */
class StackLifo extends Box {
	use RedefinableComponentTrait;

	public function push(mixed ...$in): void {
		$res = (array) $this->values;
		foreach ($in as $item) {
			$res[] = $item;
		}
		$this->exchangeArray($res);
	}

	public function pop(int $amount = 1): mixed {
		$last = $this->size - $amount;
		$res = $this->slice($last);

		$sub = $this->values;
		for ($i = $this->size - 1; $i >= $last; $i--) {
			$sub->unsetByKey($i);
		}
		$this->exchangeArray($sub);

		if ($amount === 1) {
			return $res->values[0];
		}
		return $res->values;
	}

	public function walk(): Generator {
		foreach (Math::range(0, $this->size - 1) as $i) {
			yield $this->pop();
		}
	}

	// NOTE Disabling phpcs
	// phpcs:disable

	/**
	 * @codeCoverageIgnore
	 * @return \spaf\simputils\exceptions\SortingIsNotPermitted
	 */
	private function sortingIsNotPermitted() {
		return new SortingIsNotPermitted('The sorting functionality is not allowed on Stacks');
	}

	/**
	 * @param bool      $descending     Descending
	 * @param bool      $by_values      By values
	 * @param bool      $case_sensitive Case sensitive
	 * @param bool      $natural        Natural
	 * @param ?callable $callback       Callback
	 *
	 * @codeCoverageIgnore
	 * @return \spaf\simputils\models\Box
	 * @throws \spaf\simputils\exceptions\SortingIsNotPermitted Sorting is not allowed
	 */
	#[\ReturnTypeWillChange]
	public function sort(
		bool $descending = null,
		bool $by_values = null,
		bool $case_sensitive = null,
		bool $natural = null,
		?callable $callback = null
	): Box {
		throw $this->sortingIsNotPermitted();
	}

	/**
	 * @param callable $callback Callback
	 *
	 * @codeCoverageIgnore
	 * @return bool
	 * @throws \spaf\simputils\exceptions\SortingIsNotPermitted Sorting is not allowed
	 */
	#[\ReturnTypeWillChange]
	public function uasort(callable $callback) {
		throw $this->sortingIsNotPermitted();
	}

	/**
	 * @param int $flags Flags
	 *
	 * @codeCoverageIgnore
	 * @return bool
	 * @throws \spaf\simputils\exceptions\SortingIsNotPermitted Sorting is not allowed
	 */
	#[\ReturnTypeWillChange]
	public function ksort(int $flags = SORT_REGULAR) {
		throw $this->sortingIsNotPermitted();
	}

	/**
	 * @param callable $callback Callback
	 *
	 * @codeCoverageIgnore
	 * @return bool
	 * @throws \spaf\simputils\exceptions\SortingIsNotPermitted Sorting is not allowed
	 */
	#[\ReturnTypeWillChange]
	public function uksort(callable $callback) {
		throw $this->sortingIsNotPermitted();
	}

	/**
	 * @param int $flags Flags
	 *
	 * @codeCoverageIgnore
	 * @return bool
	 * @throws \spaf\simputils\exceptions\SortingIsNotPermitted Sorting is not allowed
	 */
	#[\ReturnTypeWillChange]
	public function asort(int $flags = SORT_REGULAR) {
		throw $this->sortingIsNotPermitted();
	}

	/**
	 *
	 * @codeCoverageIgnore
	 * @return bool
	 * @throws \spaf\simputils\exceptions\SortingIsNotPermitted Sorting is not allowed
	 */
	#[\ReturnTypeWillChange]
	public function natcasesort() {
		throw $this->sortingIsNotPermitted();
	}

	/**
	 *
	 * @codeCoverageIgnore
	 * @return bool
	 * @throws \spaf\simputils\exceptions\SortingIsNotPermitted Sorting is not allowed
	 */
	#[\ReturnTypeWillChange]
	public function natsort() {
		throw $this->sortingIsNotPermitted();
	}

	// NOTE Enable phpcs back
	// phpcs:enable

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	public static function redefComponentName(): string {
		return AppInitConfig::REDEF_STACK_LIFO;
	}
}
