<?php

namespace spaf\simputils\components\filters;

use spaf\simputils\interfaces\WalkThroughFilterInterface;
use spaf\simputils\models\Box;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use function is_array;
use function str_contains;

class DirExtFilter implements WalkThroughFilterInterface {

	public function __construct(
		public array|Box|string $dirs = [],
		public array|Box|string $extensions = [],
	) {}

	public function check(File|Dir $obj): bool {
		if ($obj->type === Dir::FILE_TYPE) {
			$dirs = !is_array($this->dirs)
				?[$this->dirs]
				:$this->dirs;
			foreach ($dirs as $dir) {
				if (str_contains($obj->name_full, $dir)) {
					return true;
				}
			}
		} else {
			$extensions = !is_array($this->extensions)
				?[$this->extensions]
				:$this->extensions;
			foreach ($extensions as $ext) {
				if (str_contains($obj->extension, $ext)) {
					return true;
				}
			}
		}

		return false;
	}

	public function doSubSearch(Dir $obj): bool {
		return true;
	}
}
