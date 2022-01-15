<?php


namespace spaf\simputils\generic;



use spaf\simputils\traits\MetaMagic;
use spaf\simputils\traits\SimpleObjectTrait;

/**
 * SimpleObject
 *
 * Represents the simplest object with "getter/setter" fields control and "easy-to-prototype"
 * functionality like conversions: {@see MetaMagic::toArray()}, {@see MetaMagic::toBox()},
 * {@see MetaMagic::toJson()} and other useful perks like transparent and configurable to string
 * conversion, serialization of different kinds, etc.
 *
 * **Important:** It's not allowed to assign non-existing properties, so it's a bit more strict than
 * normal PHP Objects
 *
 * @package spaf\simputils
 */
abstract class SimpleObject {
	use SimpleObjectTrait;
	use MetaMagic;

	/**
	 * Use JSON format when converted to a string
	 *
	 * @var bool $to_string_format_json If set to true, then string format will be as "json",
	 *                                  otherwise (default) will be using object short class name
	 *                                  and object id. This variable is relevant only
	 *                                  if __toString() is not redefined, or if redefined
	 *                                  with usage of this static variable.
	 */
	public static bool $to_string_format_json = false;

	/**
	 * Improves readability through pretty JSON string format
	 *
	 * @var bool $is_json_pretty If set to true, objects that converted into JSON strings
	 *                           will be prettified (multiline string)
	 */
	public static bool $is_json_pretty = false;
}
