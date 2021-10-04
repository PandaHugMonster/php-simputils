<?php


namespace spaf\simputils\components;



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
}
