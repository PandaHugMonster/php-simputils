<?php

namespace spaf\simputils\interfaces;

use spaf\simputils\models\Dir;
use spaf\simputils\models\File;

interface WalkThroughFilterInterface {

	public function check(File|Dir $obj): bool;

	public function doSubSearch(Dir $obj): bool;
}
