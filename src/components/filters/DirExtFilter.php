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
		public array|Box|string $exts = [],
	) {}

	public function check(File|Dir $obj): bool {
		$dirs = !is_array($this->dirs)?[$this->dirs]:$this->dirs;
		$res = empty($dirs);
		foreach ($dirs as $dir) {
			$name = $obj->name_full;
			if (str_contains($name, $dir)) {
				$res = true;
			}
		}

		$res2 = true;
		if ($obj->type !== Dir::FILE_TYPE) {
			$extensions = !is_array($this->exts)?[$this->exts]:$this->exts;
			$res2 = empty($extensions);
			foreach ($extensions as $ext) {
				if ($obj?->extension === $ext) {
					$res2 = true;
				}
			}
		}

		return $res && $res2;
	}

	public function doSubSearch(Dir $obj): bool {
		return true;
	}
}
