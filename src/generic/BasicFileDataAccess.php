<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\Property;
use spaf\simputils\models\File;
use function fclose;
use function fopen;
use function fread;
use function rewind;

/**
 * @property-read File             $file
 * @property-read BasicResourceApp $app
 * @property-read resource         $fd
 * @property-read bool             $is_opened_locally
 */
abstract class BasicFileDataAccess extends SimpleObject {

	#[Property(type: 'get')]
	protected $_file = null;

	#[Property(type: 'get')]
	protected $_app = null;

	#[Property(type: 'get')]
	protected $_fd = null;

	#[Property(type: 'get')]
	protected $_is_opened_locally = null;

	/**
	 * @param File $file
	 * @param BasicResourceApp $app
	 * @param resource $fd
	 */
	function __construct($file, $app, $fd = null, $is_opened_locally = false) {
		$this->_app = $app;
		$this->_file = $file;
		$this->_fd = $fd ?? $file->fd;
		$this->_is_opened_locally = $is_opened_locally;
	}

	function ___withStart($obj, $callback) {
		$fd = $this->fd;
		$this->_is_opened_locally = false;
		if (empty($fd)) {
			$this->_is_opened_locally = true;
			$fd = fopen($this->file->name_full, 'r+');
		}

		rewind($fd);

		$callback($this);

		return true;
	}

	function ___withEnd($obj) {
		if ($this->_is_opened_locally) {
			fclose($this->fd);
		}
	}

	function rewind() {
		rewind($this->_fd);
	}

	function read($length = 1024): mixed {
		$fd = $this->_fd;
		$res = fread($fd, $length);
		if ($res === false) {
			return null;
		}
		return $res;
	}

}
