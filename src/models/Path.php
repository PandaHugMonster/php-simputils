<?php

namespace spaf\simputils\models;

use ReflectionException;
use spaf\simputils\attributes\Property;
use spaf\simputils\PHP;
use const DIRECTORY_SEPARATOR;

/**
 * @property string $dir_sep
 */
class Path extends Box {

	#[Property]
	protected string $_dir_sep = DIRECTORY_SEPARATOR;

	/**
	 * Path constructor
	 *
	 * @param string|Box|array      $path
	 * @param null|string|Box|array $work_dir
	 * @param null|string           $prefix
	 * @param string                $dir_sep
	 *
	 * @throws ReflectionException
	 */
	function __construct(
		string|Box|array      $path,
		null|string|Box|array $work_dir = null,
		?string               $prefix = null,
		string                $dir_sep = DIRECTORY_SEPARATOR,
	) {
		$array = [];
		if (PHP::isArrayCompatible($path)) {
			foreach ($path as $part) {
				$array[] = "{$part}";
			}
		} else {
			$array[] = "{$path}";
		}

		parent::__construct($array);
		$this->pathAlike();
	}

}
