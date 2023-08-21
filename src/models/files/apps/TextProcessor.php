<?php

namespace spaf\simputils\models\files\apps;

use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\models\files\apps\access\TextFileDataAccess;

/**
 * Text Processor for file
 *
 * TODO Current implementation is not efficient for Big Files (files that are bigger than
 *      amount of ram memory). Future implementation should be better in this sense.
 *
 */
class TextProcessor extends BasicResourceApp {

	public function getContent(mixed $fd, ?BasicResource $file = null): mixed {
		return stream_get_contents($fd);
	}

	/**
	 *
	 * @param mixed          $fd   Stream/Pointer/FileDescriptor/Path etc.
	 * @param mixed          $data Data to store
	 * @param ?BasicResource $file File instance
	 *
	 */
	public function setContent(mixed $fd, mixed $data, ?BasicResource $file = null): void {
		fwrite($fd, $data);
	}

	function fileDataAccessObj($file, $fd = null, $is_opened_locally = false) {
		return new TextFileDataAccess($file, $this, $fd, $is_opened_locally);
	}
}
