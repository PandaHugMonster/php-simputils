<?php

namespace spaf\simputils\models\files\apps;

use spaf\simputils\generic\BasicResourceApp;

class TextProcessor extends BasicResourceApp {

	public static function getContent(mixed $stream) {
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
}
