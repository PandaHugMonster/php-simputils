<?php

namespace spaf\simputils\models;

use spaf\simputils\components\init\AppInitConfig;

/**
 * Stack FIFO
 *
 *  First IN - First OUT
 * >====================>
 *
 */
class StackFifo extends StackLifo {

	public function pop(int $amount = 1): mixed {
		$res = $this->slice(0, $amount);

		$sub = $this->values;
		for ($i = 0; $i < $amount; $i++) {
			$sub->unsetByKey($i);
		}
		$this->exchangeArray($sub);

		if ($amount === 1) {
			return $res->values[0];
		}
		return $res->values;
	}

	public static function redefComponentName(): string {
		return AppInitConfig::REDEF_STACK_FIFO;
	}
}
