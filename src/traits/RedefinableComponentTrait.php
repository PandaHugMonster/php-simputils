<?php

namespace spaf\simputils\traits;

trait RedefinableComponentTrait {

	/**
	 * Must return redefinable component name like `InitConfig::REDEF_DATE_TIME`, etc.
	 *
	 * @return string
	 */
	abstract public static function redefComponentName(): string;
}
