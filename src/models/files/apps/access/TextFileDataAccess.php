<?php

namespace spaf\simputils\models\files\apps\access;

use spaf\simputils\generic\BasicFileDataAccess;
use function fgets;

/**
 */
class TextFileDataAccess extends BasicFileDataAccess {

	function readGroup(): mixed {
		$fd = $this->_fd;
		$res = fgets($fd);
		if ($res === false) {
			return null;
		}
		return $res;
	}

}
