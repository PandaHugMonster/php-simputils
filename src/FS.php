<?php

namespace spaf\simputils;

use Exception;
use finfo;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use function file_exists;
use function is_dir;
use const DIRECTORY_SEPARATOR;

/**
 * FileSystem class
 *
 * TODO Add fileExists method
 * TODO Add real-path property and check if realpath is the same as specified.
 */
class FS {

	/**
	 * Create file
	 *
	 * @param string|null $file_path   File path
	 * @param mixed       $content     Content to put to file
	 * @param bool        $recursively Create folders recursively
	 *
	 * @return bool|null
	 * @see \file_put_contents()
	 */
	public static function mkFile(
		?string $file_path,
		mixed $content = null,
		bool $recursively = true
	): ?bool {
		if ($recursively) {
			$base_dir = dirname($file_path);
			// Make sure the parent dir for the file is created
			static::mkDir($base_dir);
		}
		if (is_null($content))
			$content = '';
		return (bool) file_put_contents($file_path, $content);
	}

	/**
	 * Create directory
	 *
	 * @param string|null $directory_path Directory path
	 * @param bool        $recursively    Should be directories recursively created
	 *
	 * @return bool|null
	 * @see \mkdir()
	 */
	public static function mkDir(?string $directory_path, bool $recursively = true): ?bool {
		if (!file_exists($directory_path))
			return mkdir($directory_path, recursive: $recursively);
		return true;
	}

	/**
	 * List files in the folder recursively or not
	 *
	 * In case of file provided instead of folder path, will be returned an array containing
	 * just a name of the file (if not excluded).
	 *
	 * ```php
	 *      $dir = '/tmp';
	 *      $res = FS::lsFiles($dir, true, true);
	 *      print_r($res);
	 *      // Would output recursively content of your /tmp folder sorted from the top
	 *      // Equivalent of FS::lsFiles($dir, true, 'sort');
	 *
	 *      $dir = '/tmp';
	 *      $res = FS::lsFiles($dir, true, false);
	 *      print_r($res);
	 *      // Would output recursively content of your /tmp folder unsorted (on the order
	 *      // of processing/looking up)
	 *
	 *      $dir = '/tmp';
	 *      $res = FS::lsFiles($dir, true, 'rsort');
	 *      print_r($res);
	 *      // Would output recursively content of your /tmp folder in a reversed sort order
	 *
	 * ```
	 *
	 * @param ?string     $file_path             File path
	 * @param bool        $recursively           Recursively create directories
	 * @param bool|string $sorting               True/False or sorting callable
	 *                                           like "sort" or "rsort"
	 * @param bool        $exclude_original_path Exclude original file path from the array.
	 *                                           Default is true, and in the most cases it's fine.
	 *
	 * @return ?array
	 */
	public static function lsFiles(
		?string $file_path,
		bool $recursively = false,
		bool|string $sorting = true,
		bool $exclude_original_path = true
	): ?array {
		$res = $exclude_original_path?[]:[$file_path];
		if (file_exists($file_path)) {
			if (!is_dir($file_path))
				return $res;

			// TODO bug here!
			$scanned_dir = scandir($file_path);
			if ($recursively) {
				foreach ($scanned_dir as $file) {
					if (in_array($file, ['.', '..'])) continue;

					$full_sub_file_path = realpath($file_path.'/'.$file);
					$sub_list = static::lsFiles(
						$full_sub_file_path,
						$recursively,
						exclude_original_path: false
					);
					if (!empty($sub_list) && is_array($sub_list)) {
						$res = array_merge($res, $sub_list);
					}
				}
			}
		}

		if (!empty($sorting)) {
			if (Str::is($sorting) || is_callable($sorting)) {
				$sorting($res);
			} else {
				sort($res);
			}
		}

		return $res;
	}

	/**
	 * Removes only directories
	 *
	 * Recommended to use {@see static::rmFile()} instead when applicable
	 *
	 * @param string|null $directory_path Directory path
	 * @param bool        $recursively    Recursively deletes directories (required if not empty)
	 *
	 * @return bool|null
	 * @throws \Exception Exception if regular file path is supplied, and not a directory path
	 * @todo Add root dir protection
	 */
	public static function rmDir(?string $directory_path, bool $recursively = false): ?bool {
		if (!is_dir($directory_path)) {
			// TODO Fix exception
			throw new Exception("{$directory_path} is not a directory");
		}
		if ($recursively) {
			$res = false;
			$files = static::lsFiles($directory_path, true, 'rsort');
			foreach ($files as $file) {
				// Attention: Recursion is here possible in case of directories
				$res = static::rmFile($file, $recursively) || $res;
			}

			return static::rmFile($directory_path) || $res;
		}

		return rmdir($directory_path);
	}

	/**
	 * Delete file or directory
	 *
	 * This function should be used in the most cases for both deletion of regular files or
	 * directories. But, for some cases, if you want you can supply `$strict` param as true,
	 * in this case the function will delete only regular files, and raise exception if directory
	 * path is supplied.
	 *
	 * @param null|string|File $file        File path
	 * @param bool             $recursively Recursively delete files (only in case of directories)
	 * @param bool             $strict      If true supplied - then exception is raised in case of
	 *                                      directory path supplied instead of a regular file path.
	 *
	 * @return bool|null
	 * @throws \Exception Exception if `$strict` param is true and the file path provided is
	 *                    a directory.
	 */
	public static function rmFile(
		null|string|File $file,
		bool $recursively = false,
		bool $strict = false
	): ?bool {
		if (empty($file)) {
			return null;
		}

		if ($file instanceof File) {
			$file = $file->name_full;
		}

		if (!file_exists($file)) {
			return true;
		}

		if (is_dir($file)) {
			if ($strict) {
				// TODO Fix exception
				throw new Exception("{$file} is a directory, and a strict mode is on");
			} else {
				return static::rmDir($file, $recursively);
			}
		}

		return unlink($file);
	}

	public static function getFileMimeType(string|File $file, string $ext = null) {
		if ($file instanceof File && !empty($file->mime_type)) {
			return $file->mime_type;
		}

		$ext = $ext ?? pathinfo($file, PATHINFO_EXTENSION);

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
			[$_, $file_name, $_ext] = static::splitFullFilePath($file);
			if (empty($ext)) {
				$ext = $_ext; // @codeCoverageIgnore
			}
		}
		if (in_array($orig_mime, ['text/plain', 'application/x-empty'])) {
			if (in_array($ext, ['json'])) {
				return 'application/json'; // @codeCoverageIgnore
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
				return 'application/dotenv'; // @codeCoverageIgnore
			}
			if (in_array($ext, ['js'])) {
				return 'application/javascript'; // @codeCoverageIgnore
			}
			if (in_array($ext, ['csv', 'tsv'])) {
				return 'text/csv';
			}
			if (in_array($ext, ['xml'])) {
				return 'text/xml'; // @codeCoverageIgnore
			}
		}
		return $orig_mime;
	}

	/**
	 * Opposite of `splitFullFilePath()`
	 *
	 * @param string $dir  Directory
	 * @param string $name File name without extension and directory
	 * @param string $ext  Extension
	 *
	 * @see splitFullFilePath()
	 * @return string
	 */
	public static function glueFullFilePath(
		string $dir,
		string $name,
		?string $ext = null
	): string {
		if (!empty($ext)) {
			$ext = ".{$ext}";
		}
		return "{$dir}/{$name}{$ext}";
	}

	/**
	 * Splits full file path on 3 components:
	 *  * Directory
	 *  * File name without extension and directory
	 *  * Extension
	 *
	 * @param string $path Full file path
	 *
	 * @return array Array with a first item "directory",
	 *               then second "filename" and third "extension".
	 * @see \pathinfo()
	 */
	public static function splitFullFilePath(string $path): array {
		$tmp_parts = pathinfo($path);
		return [
			$tmp_parts['dirname'] ?? '',
			$tmp_parts['filename'] ?? '',
			$tmp_parts['extension'] ?? ''
		];
	}

	/**
	 * Returns File instance for the provided argument
	 *
	 * @param string|File|null $file Can be a string - then it's a path to a file, or
	 *                               a File instance, then it's just provided back
	 *                               transparently
	 * @param mixed|null       $app  Read/Write processor
	 *
	 * @return \spaf\simputils\models\File|null
	 * @throws \Exception
	 */
	public static function file(
		mixed $file = null,
		callable|string|array|BasicResourceApp $app = null
	): ?File {
		if ($file instanceof File) {
			return $file;
		}
		$class = PHP::redef(File::class);
		return new $class($file, $app);
	}

	/**
	 * @param null|string|Dir $dir
	 *
	 * FIX  Improve supported params (Directory, Files that are directories, etc. Regexp strings)
	 * @return Dir|null
	 * @throws \Exception
	 */
	public static function dir(null|string|Dir $dir = null): ?Dir {
		if ($dir instanceof Dir) {
			return $dir;
		}
		$class = PHP::redef(Dir::class);
		return new $class($dir);
	}

	/**
	 * @param string|null ...$parts
	 *
	 * TODO Implement root part somehow
	 * @return string|null
	 * @throws \Exception
	 */
	public static function path(?string ...$parts): ?string {
		$sep = DIRECTORY_SEPARATOR;
		if ($parts) {
			$res = PHP::box($parts)->join($sep);
		}
		return $res ?? null;
	}

	/**
	 * Returns file obj or path to the file relative to work-dir
	 *
	 *
	 * @param string|null ...$parts
	 *
	 * FIX  implement different plugins/modules/extensions support
	 *
	 * @return string|\spaf\simputils\models\File|\spaf\simputils\models\Dir|null
	 * @throws \Exception
	 */
	public static function locate(?string ...$parts): File|Dir {
		$work_dir = PHP::getInitConfig()->working_dir;

		$path = static::path($work_dir, ...$parts);
		if (is_dir($path)) {
			return static::dir($path);
		}

		return static::file($path);
	}
}
