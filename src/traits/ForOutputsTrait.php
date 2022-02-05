<?php

namespace spaf\simputils\traits;

use spaf\simputils\attributes\Property;

/**
 * @property-read string $for_system
 * @property-read string $for_user
 */
trait ForOutputsTrait {

	#[Property('for_system')]
	abstract protected function getForSystem(): string;

	#[Property('for_user')]
	abstract protected function getForUser(): string;
}
