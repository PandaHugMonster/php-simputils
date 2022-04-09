<?php

namespace spaf\simputils\models;

class Set extends Box {

	protected $_map_keys;
	protected $_map_values;

	public function __construct(
		object|array $array = [],
		int $flags = 0,
		string $iteratorClass = "ArrayIterator"
	) {

		parent::__construct($array, $flags, $iteratorClass);
	}

	protected function cleanUpAndCache(array $array) {

	}
}
