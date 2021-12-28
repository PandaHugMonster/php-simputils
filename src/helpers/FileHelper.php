<?php

namespace spaf\simputils\helpers;

use finfo;
use spaf\simputils\models\files\File;

class FileHelper {

	public static function getFileMimeType(string|File $file) {
		if ($file instanceof File) {
			return $file->mime_type;
		}

		$ext = pathinfo($file, PATHINFO_EXTENSION);

		if (!file_exists($file)) {
			return static::mimeTypeRealMapper('application/x-empty', $ext);
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$orig_mime = $finfo->file($file);

		return static::mimeTypeRealMapper($orig_mime, $ext);
	}

	/**
	 * @param string $orig_mime Original mime type
	 * @param string $ext       File extension
	 *
	 * TODO Must be extended further + implement dynamic replacement of the functionality
	 *      so the per-project settings could be used
	 *
	 * @return string
	 */
	protected static function mimeTypeRealMapper(string $orig_mime, string $ext): string {
		if (in_array($orig_mime, ['text/plain', 'application/x-empty'])) {
			if (in_array($ext, ['json'])) {
				return 'application/json';
			}
			if (in_array($ext, ['js'])) {
				return 'application/javascript';
			}
			if (in_array($ext, ['csv', 'tsv'])) {
				return 'text/csv';
			}
			if (in_array($ext, ['xml'])) {
				return 'text/xml';
			}
		}
		return $orig_mime;
	}
}
