<?php /** @noinspection PhpMissingParentConstructorInspection */

namespace spaf\simputils\models;

use ArrayIterator;
use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Property;
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
	 * Iterate through or Walk through the directory
	 *
	 * Walks through the elements of the folder, if `$recursively` specified, then it will be
	 * walking through all the sub-dirs (be careful, it's significantly slower!)
	 *
	 * Returned box-array that contains mix of elements of types `File` and `Dir`.
	 * Keys of those elements representing full paths.
	 *
	 * Additionally filters could be specified as strings which would be considered as
	 * regexp strings, or objects implementing
	 * `\spaf\simputils\interfaces\WalkThroughFilterInterface` interface. Multiple filters could
	 * be specified, relations between them would be "AND".
	 *
	 * For example specifying both `new OnlyDirsFilter` and `new OnlyFilesFilter` will cause
	 * processing (processing is slow), after which 0 elements will be returned always.
	 *
	 * The same time internal logic of some filters has "OR" meaning in between some elements,
	 * for example `new DirExtFilter(dirs: ['dir1', 'dir2', 'dir3'], exts: ['ts', 'js'])`
	 * will filter those as `(in "dir1" OR in "dir2" OR in "dir3") AND (has ext ".ts" OR
	 * has ext ".js").
	 *
	 * And the design above allows to do more complex combinations. For example what if you want
	 * to find all ".ts" files but those that having in their path both folders "src" and "local".
	 * Because of the default behaviour of `DirExtFilter` to consider "OR" relations between dirs
	 * you can't do that directly, though, if you add first filter like that
	 * `new DirExtFilter(dirs: 'src', exts: 'ts')` and the second filter like
	 * `new DirExtFilter(dirs: 'local')`. Then you will get meaning of:
	 * Find all ".ts" extension files in folders that has "src" AND "local" in their paths.
	 *
	 * Example:
	 * ```php
	 *
	 * $filters = [
	 *      new DirExtFilter(dirs: 'src', exts: ['ts']),
	 *      new DirExtFilter(dirs: 'local'),
	 * ];
	 *
	 * foreach ($dir->walk(true, ...$filters) as $file) {
	 *      pr("{$file}");
	 * }
	 *
	 * ```
	 *
	 * @param bool                                   $recursively Whether it should be walked
	 *                                                            recursively
	 * @param WalkThroughFilterInterface|string|null ...$filters  Filter rules, null or empty
	 *                                                            values - ignored,
	 *                                                            if string - check regexp,
	 *                                                            if not then compares complete name
	 *
	 * @see \spaf\simputils\components\filters\OnlyDirsFilter
	 * @see \spaf\simputils\components\filters\OnlyFilesFilter
	 * @see \spaf\simputils\components\filters\DirExtFilter
	 * @see \spaf\simputils\interfaces\WalkThroughFilterInterface
	 * @see \scandir()
	 * @return \spaf\simputils\models\File[]|\spaf\simputils\models\Dir[]
	 * @throws \Exception \Exception
	 */
	public function walk(
		bool $recursively = false,
		null|string|WalkThroughFilterInterface ...$filters,
	): Box|array {
		$res = new Box();
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
