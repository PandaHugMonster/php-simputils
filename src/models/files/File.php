<?php

namespace spaf\simputils\models\files;

use Exception;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\NotImplementedYet;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\helpers\FileHelper;
use spaf\simputils\models\files\apps\CsvProcessor;
use spaf\simputils\models\files\apps\DotenvProcessor;
use spaf\simputils\models\files\apps\JsonProcessor;
use spaf\simputils\models\files\apps\TextProcessor;
use spaf\simputils\PHP;
use ValueError;

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
 * @property ?BasicResourceApp $app
 * @property mixed $content Each use of the property causes real file read/write. Make sure to
 *                          cache value.
 * @property-read bool $exists
 * @property-read ?string $backup_location
 * @property-read mixed $backup_content
 */
class File extends BasicResource {

	public static array $processors = [
		// Generic text processor
		'text/plain' => TextProcessor::class,

		// JSON processors
		'application/json' => JsonProcessor::class,

		// CSV processors
		'text/csv' => CsvProcessor::class,
		'application/csv' => CsvProcessor::class,

		// DotEnv processor
		'text/dotenv' => DotenvProcessor::class,
		'application/dotenv' => DotenvProcessor::class,
	];

	/**
	 * @var bool $is_backup_preserved   When this option is set to true, then file is not deleted,
	 *                                  but relocated to "/tmp" folder with temporary random name
	 *                                  when `delete()` method is called. This allows to recover
	 *                                  file.
	 *                                  This backup functionality preserves the previous
	 *                                  last version when updated. Be careful with this option,
	 *                                  to do not cause overwhelming the target "/tmp" filesystem
	 */
	public bool $is_backup_preserved = false;

	protected $_app = null;
	protected ?string $_backup_file = null;

	/**
	 * @param null|string|resource $file Local File reference
	 *
	 * @note Currently only local files are supported
	 *
	 * @throws \spaf\simputils\exceptions\NotImplementedYet Temporary
	 */
	public function __construct(mixed $file = null, $app = null) {
		if (is_null($file)) {
			// In case of a new file, or temp file
			throw new NotImplementedYet();
		} else if (is_resource($file)) {
			throw new NotImplementedYet();
		} else if (!is_string($file)) {
			throw new ValueError('File object can receive only null|string|resource argument');
		}

		// TODO Use string for file. Implement here

		[$this->_path, $this->_name, $this->_ext] = PHP::splitFullFilePath($file);

		$this->_mime_type = FileHelper::getFileMimeType($file);
		if (empty($app)) {
			$app = static::$processors[$this->_mime_type] ?? TextProcessor::class;
		}
		$this->_app = new $app();
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
				$this->preserveFile();
			}
			return PHP::rmFile($this);
		}

		return false;
	}

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

		return PHP::glueFullFilePath(
			$dir ?? $this->path,
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
			if (rename($this->name_full, $file_path)) {
				$this->name_full = $file_path;
			}
		}

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

		if ($this->exists) {
			if ((!file_exists($file_path) || $overwrite) && copy($this->name_full, $file_path)) {
				return new static($file_path);
			}
		}

		return null;
	}

	protected function preserveFile() {
		if (empty($this->_backup_file)) {
			$this->_backup_file = tempnam('/tmp', 'simp-utils-');
			if ($this->_backup_file === false) {
				throw new Exception('Could not create a temporary file. Preserving failed');
			}
		}

		[$dir, $name, $ext] = PHP::splitFullFilePath($this->_backup_file);

		$this->copy($dir, $name, $ext,true);
	}

	#[Property('size')]
	protected function getSize(): int {
		return filesize($this->name_full);
	}

	#[Property('app')]
	protected function getResourceApp(): ?BasicResourceApp {
		return $this->_app;
	}

	#[Property('app')]
	protected function setResourceApp($var): void {
		$this->_app = $var;
	}

	#[Property('content', debug_output: false)]
	protected function getContent(): mixed {
		return $this->app::getContent($this->name_full, $this);
	}

	#[Property('content')]
	protected function setContent($data) {
		if ($this->is_backup_preserved) {
			$this->preserveFile();
		}
		$this->app::setContent($this->name_full, $data, $this);
	}

	#[Property('exists')]
	protected function getExists(): bool {
		return file_exists($this->name_full);
	}

	#[Property('backup_location')]
	protected function getBackupLocation(): ?string {
		return $this->_backup_file;
	}

	#[Property('backup_content', debug_output: false)]
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
		return $this->name_full;
	}
}
