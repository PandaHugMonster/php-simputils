<?php

namespace spaf\simputils\models;

use Closure;
use Exception;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Extract;
use spaf\simputils\attributes\Property;
use spaf\simputils\FS;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\FilesDirsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use ValueError;
use function fclose;
use function file_exists;
use function file_put_contents;
use function fopen;
use function fstat;
use function is_null;
use function rewind;
use function stat;
use function stream_get_contents;

/**
 * File representation object
 *
 * Suppose to be a bit more comfortable to use when working with files
 *
 * IMP  Internal backup system is really minimal, in the future releases it should
 *      become more enhanced.
 *
 * IMP  Each time you refer to `$this->content` property - each time file/resource
 *      is being read/written. Caching IS NOT IMPLEMENTED HERE! Please make sure you cache value
 *      by yourself!
 *
 * TODO I would not rely right now on the backup functionality at all! Only on the future releases!
 *
 * FIX  Maybe some kind of caching mechanism should be done for `$this->content` property, OR
 *      turn it back to method!
 *
 * FIX  Implement low-level format separation as "binary" and "text"
 *
 * FIX  Implement path resolution on object creation
 *
 * @property ?BasicResourceApp $app
 * @property mixed $content Each use of the property causes real file read/write. Make sure to
 *                          cache value.
 * @property-read bool $exists
 * @property-read ?string $backup_location
 * @property-read mixed $backup_content
 * @property-read Box $stat
 * @property-read string $type
 * @property-read ?DateTime $access_time
 * @property-read ?DateTime $mod_time
 * @property-read ?DateTime $inode_change_time
 * @property-read ?int $user_id
 * @property-read ?int $group_id
 * @property-read ?int $file_mode
 * @property-read ?int $links_number
 */
class File extends BasicResource {
	use RedefinableComponentTrait;
	use FilesDirsTrait;

	#[Property('type')]
	protected function getType() {
		return $this->mime_type;
	}

	/**
	 * FIX  Implement as property
	 * @var bool $is_backup_preserved   When this option is set to true, then file is not deleted,
	 *                                  but relocated to "/tmp" folder with temporary random name
	 *                                  when `delete()` method is called. This allows to recover
	 *                                  file.
	 *                                  This backup functionality preserves the previous
	 *                                  last version when updated. Be careful with this option,
	 *                                  to do not cause overwhelming the target "/tmp" filesystem
	 */
	public bool $is_backup_preserved = false;

	#[DebugHide]
	protected mixed $_app = null;
	protected bool $_is_default_app = true;
	protected ?string $_backup_file = null;

	/**
	 * Constructor
	 *
	 * Currently only local files are supported
	 *
	 * IMP  If `File` object is provided as $file argument, the result would be
	 *      the new object (basically clone/copy) and the supplied object and the current
	 *      object would have different references.
	 *      So this will not be a fully transparent approach, use `fl()` or `FS::file()`,
	 *      if you want fully transparent approach
	 *
	 * IMP  Really important to mention: This class does not do `close($fd)` for those
	 *      descriptors which it didn't open! So responsibility on opening in this case on
	 *      shoulders of users of the objects.
	 *
	 * IMP  All the mime-less files would be processing by default `TextProcessor`.
	 *
	 * @param mixed                         $file      File reference
	 * @param string|\Closure|callable|null $app       Callable/Closure or Class string that
	 *                                                 would be used for file processing (read
	 *                                                 and write)
	 * @param ?string                       $mime_type Enforcing mime type (recommended to
	 *                                                 supply it always when file descriptor
	 *                                                 or null is supplied to `$file` argument)
	 *
	 * @throws \Exception Wrong argument type
	 */
	public function __construct(
		mixed $file = null,
		null|string|Closure|callable $app = null,
		?string $mime_type = null
	) {

		if (is_null($file)) {
			// Temp file, created in memory
			// In this case you have to provide $app explicitly

			$this->_fd = fopen('php://memory', 'r+');
		} else if (is_resource($file)) {
			// If file descriptor provided
			$this->_fd = $file;
			$this->_mime_type = $mime_type;
		} else if ($file instanceof File) {
			// File instance is supplied
			if (!empty($file->fd)) {
				$this->_fd = $file->fd;
			} else {
				$this->_path = $file->path;
				$this->_name = $file->name;
				$this->_ext = $file->extension;
			}
			$this->_mime_type = $mime_type ?? $file->mime_type;
		} else if (Str::is($file)) {
			// File path is supplied
			[$this->_path, $this->_name, $this->_ext] = FS::splitFullFilePath($file);
			$this->_mime_type = $mime_type ?? FS::getFileMimeType($file);
		} else {
			throw new ValueError('File object can receive only null|string|resource|File argument');
		}


		// FIX  Reconsider the code
		if (empty($app) || Str::is($app)) {
			if (!empty($app)) {
				$this->_is_default_app = false; // @codeCoverageIgnore
			}
			$app = static::getCorrespondingProcessor($this->name_full, $this->mime_type, $app);
		}

		$this->_app = $app;
	}

	/**
	 * Deletes/Removes file from file system or storage
	 *
	 * @param bool $i_am_sure Without this parameter set to true, file will not be deleted!
	 *
	 * @return bool
	 * @throws \Exception Problems with deleting
	 */
	public function delete(bool $i_am_sure = false): bool {
		if ($i_am_sure) {
			if ($this->is_backup_preserved) {
				$this->preserveFile(); // @codeCoverageIgnore
			}
			return FS::rmFile($this);
		}

		return false;
	}

	/**
	 * @codeCoverageIgnore
	 * @return void
	 * @throws \spaf\simputils\exceptions\NotImplementedYet
	 */
	public function recoverFromBackup() {
		if ($this->is_backup_preserved) {
			if (empty($this->_backup_file) || !file_exists($this->_backup_file)) {
				throw new Exception('No backup file exists.');
			}

			// Preparing for swapping
			$tmp_file = new static($this->_backup_file);
			$tmp_file->move(ext: "{$tmp_file->extension}-ready");

			// Preserving current content
			$this->preserveFile();

			// Moving prepared file back to the main place
			$tmp_file->move($this->path, $this->name, $this->extension, true);

			// Swapping is done here
		}
	}

	private function _prepareCopyMoveDest($dir, $name, $ext): string {
		if (empty($dir) && empty($name) && empty($ext)) {
			throw new Exception(
				'File destination does not differ from the source destination'
			);
		}

		if (empty($dir)) {
			$dir = null;
		}

		return FS::glueFullFilePath(
			$dir ?? $this->path ?? PHP::getInitConfig()->working_dir,
			$name ?? $this->name,
			$ext ?? $this->extension
		);
	}

	/**
	 * Relocates/Moves file to the new place
	 *
	 * Returns the same File object
	 *
	 * If some of the main params are skipped, they are picked from the current values of the object
	 *
	 * **Important:** If you trying to move file-system non-existing file (non-ram file),
	 * it will not create anything, it will just adjust all the required things like file-name/path
	 * and in case of default app - it will set an appropriate app (but only if it was not
	 * explicitly set!)
	 *
	 * @param ?string $new_location_dir New location path (location dir, not the full path!)
	 * @param ?string $name             Filename without extension and path
	 * @param ?string $ext              Extension
	 * @param bool    $overwrite        Is allowed the existing file to be overwritten
	 *
	 * @return $this
	 * @throws \Exception Problems with moving
	 */
	public function move(
		?string $new_location_dir = null,
		?string $name = null,
		?string $ext = null,
		bool $overwrite = false
	): self {
		$file_path = $this->_prepareCopyMoveDest($new_location_dir, $name, $ext);

		if (!file_exists($file_path) || $overwrite) {
			if (!empty($fd = $this->_fd)) {
				rewind($fd);
				$res = stream_get_contents($fd);
				if (file_put_contents($file_path, $res)) {
					// Swittching context to use real file (through file path and name)
					fclose($this->_fd);
					$this->_fd = null;
				}
			} else if (rename($this->name_full, $file_path)) {
//				[$this->_path, $this->_name, $this->_ext] = $split_data;
			}
		}

		[$this->_path, $this->_name, $this->_ext] = FS::splitFullFilePath($file_path);

		if ($this->_is_default_app) {
			// If app was not explicitly set, we adjust default app for the file
			$this->_app = static::getCorrespondingProcessor($this->name_full);
		}

		$this->_mime_type = FS::getFileMimeType($this->name_full);

		return $this;
	}

	/**
	 * Copies the current File object content to a new location
	 *
	 * Returns null or a new File object of the copy-file
	 *
	 * If some of the main params are skipped, they are picked from the current values of the object
	 *
	 * @param ?string $new_location_dir New location path (location dir, not the full path!)
	 * @param ?string $name             Filename without extension and path
	 * @param ?string $ext              Extension
	 * @param bool    $overwrite        Is allowed the existing file to be overwritten
	 *
	 * @return ?static
	 * @throws \spaf\simputils\exceptions\NotImplementedYet Temporary
	 */
	public function copy(
		?string $new_location_dir = null,
		?string $name = null,
		?string $ext = null,
		bool $overwrite = false
	): ?static {
		$file_path = $this->_prepareCopyMoveDest($new_location_dir, $name, $ext);

		if (!file_exists($file_path) || $overwrite) {
			if (!empty($fd = $this->fd)) {
				// $split_data = FS::splitFullFilePath($file_path);
				rewind($fd);
				$res = stream_get_contents($fd);
				if (file_put_contents($file_path, $res)) {
					return new static($file_path);
					// [$this->_path, $this->_name, $this->_ext] = $split_data;
				}
			} else if (copy($this->name_full, $file_path)) {
				return new static($file_path);
			}
		}

		return null;
	}

	/**
	 * @codeCoverageIgnore
	 * @return void
	 * @throws \spaf\simputils\exceptions\NotImplementedYet
	 */
	protected function preserveFile() {
		if (empty($this->_backup_file)) {
			$this->_backup_file = tempnam('/tmp', 'simp-utils-');
			if ($this->_backup_file === false) {
				throw new Exception('Could not create a temporary file. Preserving failed');
			}
		}

		[$dir, $name, $ext] = FS::splitFullFilePath($this->_backup_file);

		$this->copy($dir, $name, $ext,true);
	}

	#[Property('links_number')]
	protected function getLinksNumber(): ?int {
		return $this->stat->get('nlink');
	}

	#[Property('file_mode')]
	protected function getFileMode(): ?int {
		return $this->stat->get('mode');
	}

	#[Property('user_id')]
	protected function getUserId(): ?int {
		return $this->stat->get('uid');
	}

	#[Property('group_id')]
	protected function getGroupId(): ?int {
		return $this->stat->get('gid');
	}

	#[Property('inode_change_time')]
	protected function getInodeChangeTime(): ?DateTime {
		$val = $this->stat->get('ctime');
		if (!is_null($val)) {
			return PHP::ts($val);
		}
		return null;
	}

	#[Property('mod_time')]
	protected function getModTime(): ?DateTime {
		$val = $this->stat->get('mtime');
		if (!is_null($val)) {
			return PHP::ts($val);
		}
		return null;
	}

	#[Property('access_time')]
	protected function getAccessTime(): ?DateTime {
		$val = $this->stat->get('atime');
		if (!is_null($val)) {
			return PHP::ts($val);
		}
		return null;
	}

	#[Property('stat')]
	protected function getStat(): ?Box {
		$class_box = PHP::redef(Box::class);
		if (!empty($this->_fd)) {
			return new $class_box(fstat($this->_fd));
		}

		if ($this->exists) {
			return new $class_box(stat($this->name_full));
		}

		return null;
	}

	#[Property('size')]
	protected function getSize(): ?int {
		return $this->stat['size'] ?? null;
	}

	#[Property('app')]
	protected function getResourceApp(): null|Closure|array|BasicResourceApp {
		return $this->_app;
	}

	#[Property('app')]
	protected function setResourceApp(null|Closure|array|BasicResourceApp $var): void {
		$this->_app = $var;
	}

	#[DebugHide(false)]
	#[Property('content')]
	protected function getContent(): mixed {
		$app = $this->app;
		$is_opened_locally = false;
		$fd = $this->fd;

		if (empty($fd)) {
			$is_opened_locally = true;
			if (!$this->exists) {
				return null;
			}
			$fd = fopen($this->name_full, 'r');
		}

		$meta = stream_get_meta_data($fd);
		if ($meta['seekable']) {
			rewind($fd);
		}
		$res = $app($this, $fd, true, null);

		if ($is_opened_locally) {
			fclose($fd);
		}

		return $res;
	}

	#[Property('content')]
	protected function setContent($data) {
		if ($this->is_backup_preserved) {
			$this->preserveFile(); // @codeCoverageIgnore
		}
		$app = $this->app;
		$is_opened_locally = false;
		$fd = $this->fd;

		if (empty($fd)) {
			$is_opened_locally = true;
			$fd = fopen($this->name_full, 'w');
		}

		rewind($fd);
		$app($this, $fd, false, $data);

		if ($is_opened_locally) {
			fclose($fd);
		}
		if (!empty($this->name_full)) {
			$this->_mime_type = FS::getFileMimeType($this->name_full);
		}
	}

	#[Property('exists')]
	protected function getExists(): bool {
		return !empty($this->name_full) && file_exists($this->name_full);
	}

	/**
	 * @codeCoverageIgnore
	 * @return string|null
	 */
	#[Extract(false)]
	#[Property('backup_location')]
	protected function getBackupLocation(): ?string {
		return $this->_backup_file;
	}

	/**
	 * @codeCoverageIgnore
	 * @return string|null
	 * @throws \Exception
	 */
	#[Extract(false)]
	#[DebugHide(false)]
	#[Property('backup_content')]
	protected function getBackupContent(): ?string {
		if (file_exists($this->_backup_file)) {
			return (new static($this->_backup_file))->content;
		}

		return null;
	}

	//// Some Magic and MetaMagic

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->name_full ?? "{$this->_fd}";
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	public static function redefComponentName(): string {
		return InitConfig::REDEF_FILE;
	}
}
