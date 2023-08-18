<?php

namespace spaf\simputils;

use Closure;
use finfo;
use ReflectionMethod;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\exceptions\CannotDeleteDirectory;
use spaf\simputils\exceptions\DataDirectoryIsNotAllowed;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\models\Box;
use spaf\simputils\models\Dir;
use spaf\simputils\models\File;
use spaf\simputils\models\files\apps\PHPFileProcessor;
use function file_exists;
use function is_array;
use function is_callable;
use function is_dir;
use function spaf\simputils\basic\ic;
use const DIRECTORY_SEPARATOR;

/**
 * FileSystem class
 *
 * TODO Add fileExists method
 * TODO Add real-path property and check if realpath is the same as specified.
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) Does not matter for Core Helper Classes
 */
class FS {

	/**
	 * Require (working-dir relative)
	 *
	 * @param array|Box|File $file File ref
	 * @see FS::file()
	 * @see File
	 * @see FS::locate()
	 * @return mixed
	 */
	#[Shortcut('require', 'but relative')]
	static function require(array|Box|File $file) {
		return require FS::file($file);
	}

	/**
	 * Include (working-dir relative)
	 *
	 * @param array|Box|File $file File ref
	 * @see FS::file()
	 * @see File
	 * @see FS::locate()
	 * @return mixed
	 */
	#[Shortcut('include', 'but relative')]
	static function include(array|Box|File $file) {
		return include FS::file($file);
	}

	/**
	 * Returns data from a file (PHP, or any other)
	 *
	 * IMP  It's always recommended to use this method for all in-code data
	 *      gathering from files. (It's highly preferable instead of require or
	 *      include for data files!)
	 *
	 * instead of `$t = require '........')` and instead of file objects usages
	 * for other types of files (json, csv, xml, ...)
	 *
	 * This method is highly convenient, and comfortable to use.
	 *
	 * It uses `require`.
	 *
	 * Important, it will not let you access file, unless it's in allowed data-directory
	 * set {@see \spaf\simputils\models\InitConfig::$allowed_data_dirs} (they should be relative
	 * to working dir paths, no absolute-paths are supported this way)
	 *
	 * IMP  If for some reason you need "absolute paths" for `InitConfig::$allowed_data_dirs`
	 *      then just use files directly. It's strongly recommended against it, but you
	 *      have options if you really need it.
	 *      The data() method works only within projects working-dir
	 *
	 * IMP  If file does not exist `null` is returned
	 *
	 * Example:
	 * ```php
	 *  PHP::init([
	 *      'allowed_data_dirs' => [
	 *          'data/data1',
	 *          'data/data2/data4', // If you would comment this out, then exception will be raised
	 *      ]
	 *  ]);
	 *  $data = FS::data(['data', 'data2', 'data4', 'exp.php']);
	 *  pd($data);
	 * ```
	 *
	 * @param array|Box|File $file File ref
	 * @param ?string        $ic   Init Config name (default - is referenced to the app,
	 *                             other non-empty names can be used by
	 *                             the libraries/plugins)
	 *
	 * @return mixed
	 * @throws \spaf\simputils\exceptions\DataDirectoryIsNotAllowed If directory was not
	 *                                                              beforehand allowed
	 *                                                              in init-config
	 * @see FS::include()
	 * @see FS::file()
	 * @see File
	 * @see FS::locate()
	 * @see FS::require()
	 */
	static function data(array|Box|File $file, null|string|BasicInitConfig $ic = null) {
		return static::dataFile($file, $ic)?->content ?? null;
	}

	static function dataFile(array|Box|File $file, null|string|BasicInitConfig $ic = null): ?File {
		$file = FS::file($file);

		if (empty($ic)) {
			$ic = ic();
		} else if (Str::is($ic)) {
			$ic = ic($ic);
		}

		$is_allowed = false;
		foreach ($ic->allowed_data_dirs as $dir) {
			$dir = FS::locate($dir);
			if (Str::startsWith($file, $dir)) {
				$is_allowed = true;
				break;
			}
		}
		if (!$is_allowed) {
			throw new DataDirectoryIsNotAllowed('Access to '.$file.' is ' .
				'not allowed, because it\'s not in the allowed data-directory');
		}

		$mt = PHP::listOfExecPhpMimeTypes();
		$pe = PHP::listOfExecPhpFileExtensions();
		if ($mt->containsValue($file->mime_type) || $pe->containsValue($file->extension)) {
			if (!$file->exists) {
				return null;
			}
			$reflection = new ReflectionMethod(BasicResource::class, 'setIsExecutableProcessingEnabled');
			$reflection->setAccessible(true);
			$reflection->invoke($file, true);
			$reflection->setAccessible(false);
			$file->app = new PHPFileProcessor();
		}

		return $file;
	}

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
		bool|string|callable $sorting = true,
		bool $exclude_original_path = true
	): ?array {
		$res = $exclude_original_path
			?[]
			:[$file_path];

		if (file_exists($file_path)) {
			if (!is_dir($file_path)) {
				return $res;
			}

			// TODO bug here!
			$scanned_dir = scandir($file_path);
			if ($recursively) {
				foreach ($scanned_dir as $file) {
					if (in_array($file, ['.', '..'])) {
						continue;
					}

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

		$res = static::applySorting($res, $sorting);

		return $res;
	}

	protected static function applySorting(Box|array $files, bool|string|callable $sorting) {
		if (!empty($sorting)) {
			if (Str::is($sorting) || is_callable($sorting)) {
				$sorting($files);
			} else {
				sort($files);
			}
		}
		return $files;
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
	 * @todo Add root dir protection
	 */
	public static function rmDir(?string $directory_path, bool $recursively = false): ?bool {
		if (!is_dir($directory_path)) {
			// TODO Fix exception
			throw new CannotDeleteDirectory("{$directory_path} is not a directory");
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
				throw new CannotDeleteDirectory("{$file} is a directory, and a strict mode is on");
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

	protected static function extractNameAndExtFromFile($file, $ext = null) {
		$file_name = null;
		if (!empty($file)) {
			[$_, $file_name, $_ext] = static::splitFullFilePath($file);
			if (empty($ext)) {
				$ext = $_ext; // @codeCoverageIgnore
			}
		}
		return [$file_name, $ext];
	}

	/**
	 * @param $file_name
	 * @param $ext
	 *
	 * DotEnv files are extremely loosely defined
	 * TODO Implement detailed description/documentation compiled from all other
	 *      languages implementations. Maybe define a specification of that compilation
	 *
	 * @return bool
	 */
	protected static function checkDotEnvMime($file_name, $ext): bool {
		return (empty($file_name) && str_starts_with($ext, 'env'))
			|| (!empty($file_name) && str_starts_with($file_name, '.env'));
	}

	protected static function checkPhpMime($file_name, $ext): bool {
		return PHP::listOfExecPhpFileExtensions()->containsValue($ext);
	}

	protected static function identifyMimeByExt($file_name, $ext) {
		$ext_to_mime_mapping = PHP::box([
			'application/json' => ['json', ],
			'application/dotenv' => [
				'env',
				Closure::fromCallable([static::class, 'checkDotEnvMime']),
			],
			'application/javascript' => ['js', ],
			'text/csv' => ['csv', 'tsv', ],
			'text/xml' => ['xml', ],
			'application/x-php' => [
				Closure::fromCallable([static::class, 'checkPhpMime']),
			],
		]);
		foreach ($ext_to_mime_mapping as $mime => $possibles) {
			foreach ($possibles as $item) {
				/** @var callable|string $item */
				if ($item instanceof Closure) {
					if ($item($file_name, $ext)) {
						return $mime;
					}
				} else if ("{$item}" === $ext) {
					return $mime;

				}
			}
		}

		return null;
	}

	/**
	 * @param string  $orig_mime Original mime type
	 * @param string  $ext       File extension
	 * @param ?string $file      File path
	 *
	 * TODO Must be extended further + implement dynamic replacement of the functionality
	 *      so the per-project settings could be used
	 *
	 * TODO Subject to serious optimization! Currently extremely messy
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
		[$file_name, $ext] = static::extractNameAndExtFromFile($file, $ext);

		if (in_array($orig_mime, ['text/plain', 'application/x-empty'])) {
			if ($final_mime = static::identifyMimeByExt($file_name, $ext)) {
				return $final_mime;
			}
		}
		return $orig_mime;
	}

	/**
	 * Opposite of `splitFullFilePath()`
	 *
	 * @param string  $dir  Directory
	 * @param string  $name File name without extension and directory
	 * @param ?string $ext  Extension
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
	 * @param string|resource|int|Box|array|File|null $file Can be a string - then it's a path
	 *                                                      to a file, or a File instance, then
	 *                                                      it's just provided back transparently
	 * @param mixed|null                              $app  Read/Write processor
	 *
	 * @return File|null
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
	 * @param null|string|Dir $dir Directory
	 *
	 * TODO Improve supported params (Directory, Files that are directories, etc. Regexp strings)
	 * @return Dir|null
	 */
	public static function dir(null|string|Dir $dir = null): ?Dir {
		if ($dir instanceof Dir) {
			return $dir;
		}
		$class = PHP::redef(Dir::class);
		return new $class($dir);
	}

	/**
	 * @param string|null ...$parts Parts that should be joined depending on the platform
	 *
	 * TODO Implement root part somehow
	 * TODO Windows is not tested, and might cause errors for now
	 * @return string|null
	 */
	public static function path(?string ...$parts): ?string {
		// FIX  Refactor mechanics to use new one
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
	 * @param mixed ...$parts Parts that should be joined depending on the platform
	 *
	 * TODO Implement assoc args as parameters. Implement such "working_dir" arg
	 *      Implement such "working_dir" arg and implement "name" of init-config
	 * TODO Maybe implement transparent support of files as arguments
	 *
	 * @return File|Dir
	 */
	public static function locate(mixed ...$parts): File|Dir {
		$work_dir = PHP::getInitConfig()->working_dir;

		$path = static::path($work_dir, ...$parts);
		if (is_dir($path)) {
			return static::dir($path);
		}

		return static::file($path);
	}
}
