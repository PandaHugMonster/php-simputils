<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\FS;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\PHP;
use function getcwd;
use function is_array;
use function is_null;
use function is_string;

/**
 *
 */
class InitConfig extends BasicInitConfig {

	#[Property(type: 'get')]
	protected ?Dir $_code_root = null;

	#[Property(type: 'get')]
	protected ?Dir $_working_dir = null;

	function __construct(null|array|Box $args = null) {
		if (!is_null($args)) {
			$args = PHP::box($args);
			$this->code_root = $args->get('code_root');
			$this->working_dir = $args->get('working_dir');
		} else {
			$this->code_root = null;
			$this->working_dir = null;
		}
	}

	#[Property('code_root', 'set')]
	protected function setCodeRoot(null|string|array|Box|Dir $val) {
		$this->_code_root = static::_preParseDir($val);
	}

	private static function _preParseDir(null|string|array|Box|Dir $val): Dir {
		if (is_string($val)) {
			$dir = FS::dir($val);
		} else if ($val instanceof Dir) {
			$dir = FS::dir("{$val}");
		} else if (PHP::isArrayCompatible($val)) {
			if (is_array($val)) {
				$val = PHP::box($val);
			}
			$dir = FS::dir("{$val->pathAlike()}");
		} else {
			$dir = FS::dir(getcwd());
		}

		return $dir;
	}

	#[Property('working_dir', 'set')]
	protected function setWorkingDir(null|string|array|Box|Dir $val) {
		$this->_working_dir = static::_preParseDir($val);
	}

}
