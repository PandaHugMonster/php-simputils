<?php

namespace spaf\simputils\components\filters;

use spaf\simputils\interfaces\WalkThroughFilterInterface;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;

class OnlyDirsFilter implements WalkThroughFilterInterface {

	public function check(Dir|File $obj): bool {
		return $obj->type === Dir::FILE_TYPE;
	}

	public function doSubSearch(Dir $obj): bool {
		return true;
	}
}
