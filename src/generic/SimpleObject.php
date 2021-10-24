<?php


namespace spaf\simputils\generic;



use spaf\simputils\interfaces\SimpleObjectInterface;
use spaf\simputils\traits\MetaMagic;
use spaf\simputils\traits\SimpleObjectTrait;

/**
 * SimpleObject
 *
 * Basically represents the simplest object with "getter/setter" properties control,
 * and strict access check to those properties.
 *
 * It's not allowed to assign non-existing properties, so it's a bit more strict than
 * normal PHP Objects
 *
 * @package spaf\simputils
 */
abstract class SimpleObject implements SimpleObjectInterface {
	use SimpleObjectTrait;
	use MetaMagic;

	/**
	 * @var bool If set to true, then string format will be as "json", otherwise (default)
	 *           will be using object short class name and object id. This variable is relevant
	 *           only if __toString() is not redefined, or if redefined with usage of this static
	 *           variable.
	 */
	public static bool $to_string_format_json = false;
	public static bool $is_json_pretty = false;
}
