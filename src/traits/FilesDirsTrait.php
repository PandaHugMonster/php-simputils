<?php

namespace spaf\simputils\traits;

use spaf\simputils\PHP;
use spaf\simputils\Str;
use function str_starts_with;
use function substr;

trait FilesDirsTrait {

//	#[Property('name')]
//	protected function getName() {
//		return basename($this->_core_dir);
//	}
//
//	#[Property('exists')]
//	protected function getExists() {
//		return file_exists($this->_core_dir);
//	}
//
//	#[Property('type')]
//	protected function getType() {
//		return static::FILE_TYPE;
//	}

	/**
	 * Format file path string
	 *
	 * @param int $relativity If 0 then returns only filename, if >0 then adjusting relativity
	 * of the path from the root side (left part of the string), if <0 then adjusting relativity
	 * of the path from the file name side (right part of the string before filename).
	 *
	 * For the file path: "/one/two/three/four/five/six/my-file.txt"
	 * For example:
	 * ```php
	 *      echo $dir->format();
	 * ```
	 * would return filename `my-file.txt`
	 *
	 * if negative number supplied
	 * ```php
	 *      echo $dir->format(-2);
	 * ```
	 * would return `five/six/my-file.txt` string (2 parent-dirs included)
	 *
	 * if positive number supplied
	 * ```php
	 *      echo $dir->format(2);
	 * ```
	 * would return `three/four/five/six/my-file.txt` string (all parent-dirs included except
	 * very first 2)
	 *
	 * If string provided and it equals the left side of the path, then it would be cut out
	 * from the left side. So considering that the resulting string should be relative to
	 * the provided root string.
	 *
	 *
	 */
	public function format(int|string $relativity = 0, bool $include_ext = true): string {
		$sep = '/';
		$ext = $this?->extension ?? null;
		$ext = $include_ext && $ext?".{$ext}":null;

		if (Str::is($relativity)) {
			$relativity = preg_replace('#'.$sep.'+#', $sep, "{$sep}{$relativity}{$sep}");
			if (str_starts_with($this->_path, $relativity)) {
				return substr($this->_path, Str::len($relativity)).$sep.$this->name.$ext;
			}

			return $this->name.$ext;
		}
		if ($relativity === 0) {
			return $this->name.$ext;
		}

		$path = preg_replace('#'.$sep.'+#', $sep, $this->_path);
		$path = $path[0] === $sep
			?substr($path, 1)
			:$path;
		$res = PHP::box(explode($sep, $path));
		return implode($sep, (array) $res->slice($relativity)).$sep.$this->name.$ext;
	}
}
