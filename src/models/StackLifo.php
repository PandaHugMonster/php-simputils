<?php

namespace spaf\simputils\models;

use Generator;
use spaf\simputils\Math;
use spaf\simputils\traits\RedefinableComponentTrait;

/**
 * Stack LIFO
 *
 *  Last IN - First OUT
 * |===================<>
 *
 *
 * FIX  Block sorting functionalities
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

	public static function redefComponentName(): string {
		return InitConfig::REDEF_STACK_LIFO;
	}
}
