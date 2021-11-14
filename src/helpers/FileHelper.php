<?php

namespace spaf\simputils\helpers;

use finfo;
use spaf\simputils\models\files\File;

class FileHelper {

	public static function getFileMimeType(string|File $file) {
		if ($file instanceof File) {
			return $file->mime_type;
		}

		if (!file_exists($file)) {
			return null;
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$orig_mime = $finfo->file($file);

		$ext = pathinfo($file, PATHINFO_EXTENSION);

		return static::mimeTypeRealMapper($orig_mime, $ext);
	}

	/**
	 * @param $orig_mime
	 * @param $ext
	 *
	 * FIX  Just a prototype yet
	 * @return mixed
	 */
	protected static function mimeTypeRealMapper($orig_mime, $ext) {
		if (in_array($orig_mime, ['text/plain', 'application/x-empty'])) {
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
