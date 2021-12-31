<?php

namespace spaf\simputils\generic;

use spaf\simputils\models\Box;

/**
 *
 */
abstract class BasicInitConfig extends SimpleObject {

	public ?string $name = null;
	public ?string $code_root = null;
	public ?string $working_dir = null;

	/**
	 * @var array|Box|null $init_blocks List of classes FQNs (those classes must implement
	 *                                  interface `\spaf\simputils\interfaces\InitBlockInterface`)
	 */
	public null|array|Box $init_blocks = [];



	public function __toString(): string {
		$init_blocks = $this->init_blocks;
		if (!$init_blocks instanceof Box) {
			$init_blocks = new Box($init_blocks);
		}
		return  "{$this->class_short}[name={$this->name}, code_root={$this->code_root}, " .
				"working_dir={$this->working_dir}, init_blocks={$init_blocks}]";
	}
}
