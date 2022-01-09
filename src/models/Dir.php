<?php /** @noinspection PhpMissingParentConstructorInspection */

namespace spaf\simputils\models;

use ArrayIterator;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
use spaf\simputils\components\SimpUtilsDirectoryIterator;
use spaf\simputils\FS;
use spaf\simputils\interfaces\WalkThroughFilterInterface;
use spaf\simputils\traits\FilesDirsTrait;
use spaf\simputils\traits\RedefinableComponentTrait;
use function basename;
use function dirname;
use function file_exists;
use function is_dir;
use function preg_match;
use function realpath;
use function scandir;

/**
 *
 * FIX  Maybe do not extend from Box because of stupid PHP bug :(
 * @property-read $exists
 * @property-read $type
 * @property-read ?string $name
 * @property-read ?string $name_full
 */
class Dir extends Box {
	use RedefinableComponentTrait;
	use FilesDirsTrait;

	const FILE_TYPE = 'directory';

	#[DebugHide]
	protected ?string $_path = null;
	#[DebugHide]
	protected ?string $_name = null;

	#[Property('name')]
	protected function getName() {
		return basename($this->_name);
	}

	#[Property('name_full')]
	protected function getNameFull(): ?string {
		if (empty($this->_path) && empty($this->_name)) {
			return null;
		}
		return FS::glueFullFilePath($this->_path, $this->_name);
	}

	#[Property('path')]
	protected function getPath(): ?string {
		return $this->_path;
	}

	#[Property('exists')]
	protected function getExists() {
		return file_exists($this->name_full);
	}

	#[Property('type')]
	protected function getType() {
		return static::FILE_TYPE;
	}

	/**
	 * @param bool              $recursively       Whether it should be walked recursively
	 * @param array|string|null $pattern            Filter rules, null or empty values - ignored,
	 *                                             if string - check regexp, if not then compares
	 *                                             complete name
	 * @param bool              $show_hidden_files Whether to show files/dirs prefixed with '.'
	 *                                             except self references like '.' and '..', those
	 *                                             are always excluded
	 *
	 * @return \spaf\simputils\models\File[]|\spaf\simputils\models\Dir[]
	 * @throws \Exception
	 */
	public function walk(
		bool $recursively = false,
		null|string|WalkThroughFilterInterface ...$filters,
	) {
		$res = [];
		if ($dir = scandir($this->name_full)) {
			foreach ($dir as $item) {
				if ($item === '.' || $item === '..') {
					continue;
				}

				$full_path = "{$this->name_full}/{$item}";

				$obj = is_dir($full_path)
					?new Dir($full_path)
					:new File($full_path);


				$include = true;
				$do_subs = true;
				foreach ($filters as $filter) {
					if (empty($filter)) {
						continue;
					} else if ($filter instanceof WalkThroughFilterInterface) {
						if (!$filter->check($obj)) {
							$include = false;
						}
						$do_subs = $obj->type === Dir::FILE_TYPE && $filter->doSubSearch($obj);
					} else if (!preg_match($filter, $full_path)) {
						$include = false;
						$do_subs = false;
					}
				}

				if ($include) {
					$res[$full_path] = $obj;
				}

				if ($recursively && $do_subs && $obj->type === Dir::FILE_TYPE) {
					$sub_stuff = $obj->walk($recursively, ...$filters);
					foreach ($sub_stuff as $key => $sub) {
						$res[$key] = $sub;
					}
				}
			}
		}
		return $res;
	}

	/**
	 * Creates Dir object
	 *
	 * '.' and '..' always refers to the directory where the current executing file is located!
	 *
	 * @param string|null $dir Directory, if file provided, will be used it's folder
	 */
	public function __construct(null|string $dir = '.') {
		$rp = realpath($dir);
		if (!is_dir($rp)) {
			$rp = dirname($rp);
		}
		$this->_path = dirname($rp);
		$this->_name = basename($rp);
	}

	public function getIterator() {
		return new ArrayIterator($this->walk(false));
	}

	public function __toString(): string {
		return $this->name_full;
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_DIR;
	}
}
