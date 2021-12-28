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

	public static function getContent(mixed $stream, ?BasicResource $file = null): mixed {

		if (is_string($stream)) {
			$fd = fopen($stream, 'r');
		} else if (is_resource($stream)) {
			$fd = $stream;
		}

		$content = stream_get_contents($fd);

		if (is_string($stream)) {
			fclose($fd);
		}

		return $content;
	}

	public static function setContent(mixed $stream, $data, ?BasicResource $file = null): void {
		if (is_string($stream)) {
			$fd = fopen($stream, 'w');
		} else if (is_resource($stream)) {
			$fd = $stream;
		}

		fwrite($fd, $data);

		if (is_string($stream)) {
			fclose($fd);
		}
	}
}
