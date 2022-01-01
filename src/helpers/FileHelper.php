<?php

namespace spaf\simputils\helpers;

use finfo;
use spaf\simputils\models\File;
use spaf\simputils\PHP;

class FileHelper {

	public static function getFileMimeType(string|File $file) {
		if ($file instanceof File) {
			return $file->mime_type;
		}

		$ext = pathinfo($file, PATHINFO_EXTENSION);

		if (!file_exists($file)) {
			return static::mimeTypeRealMapper('application/x-empty', $ext, $file);
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$orig_mime = $finfo->file($file);

		return static::mimeTypeRealMapper($orig_mime, $ext, $file);
	}

	/**
	 * @param string  $orig_mime Original mime type
	 * @param string  $ext       File extension
	 * @param ?string $file      File path
	 *
	 * TODO Must be extended further + implement dynamic replacement of the functionality
	 *      so the per-project settings could be used
	 * FIX  Subject to serious optimization! Currently extremely messy
	 *
	 * TODO Consider extracting particular mime-type identification into "processors" code,
	 *      when those processors are registered in the framework (even external ones)
	 *
	 * @return string
	 */
	protected static function mimeTypeRealMapper(
		string $orig_mime,
		string $ext,
		string $file = null
	): string {
		$file_name = null;
		if (!empty($file)) {
			[$_, $file_name, $_ext] = PHP::splitFullFilePath($file);
			if (empty($ext)) {
				$ext = $_ext;
			}
		}
		if (in_array($orig_mime, ['text/plain', 'application/x-empty'])) {
			if (in_array($ext, ['json'])) {
				return 'application/json';
			}
			$check = in_array($ext, ['env']);
			$check = $check || (
				(empty($file_name) && str_starts_with($ext, 'env')) ||
				(!empty($file_name) && str_starts_with($file_name, '.env'))
			);

			if ($check) {
				// DotEnv files are extremely loosely defined
				// FIX  Implement detailed description/documentation compiled from all other
				//      languages implementations. Maybe define a specification of that compilation
				return 'application/dotenv';
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
