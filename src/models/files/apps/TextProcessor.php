<?php

namespace spaf\simputils\models\files\apps;

use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;

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
	public function setContent(mixed $fd, $data, ?BasicResource $file = null): void {
		fwrite($fd, $data);
	}
}
