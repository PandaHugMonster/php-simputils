<?php


namespace spaf\simputils\traits\logger;


use spaf\simputils\attributes\Property;
use spaf\simputils\FS;

trait LoggerBasicFileOutputTrait {

	#[Property('storage')]
	abstract public function getStorage(): string;

	// FIX  Something terrible!!! Renamed method
	#[Property('file_name')]
	abstract public function getFileName(int $number = 0): string;

	public function __construct(?string $storage = null, ?string $prefix = null, ?string $ext = null) {
		if (!empty($storage))
			$this->storage = $storage;
		if (!empty($prefix))
			$this->file_name_prefix = $prefix;
		if (!empty($ext))
			$this->file_name_ext = $ext;
	}

	public function saveFile(string $data_str) {
		$path = $this->composeFilePath();
		$this->prepareStorage($path);

		FS::mkFile($path, $data_str);
	}

	public function addToFile(string $data_str) {
		$path = $this->composeFilePath();
		$this->prepareStorage($path);

		$fd = fopen($path, 'a');
		fwrite($fd, "{$data_str}\n");
		fclose($fd);
		if ($this->clear_file_stat_cache)
			clearstatcache();
	}

	protected function composeFilePath(int $number = 0): string {
		$storage = $this->getStorage();
		$file_name = $this->getFileName($number);
		$path = "{$storage}/{$file_name}";
		return $path;
	}

	protected function prepareStorage($path) {
		if ($this->is_structure_auto_created) {
			$basedir = dirname($path);
			FS::mkDir($basedir, true);
		}
	}

	protected function rotateFiles() {
		for ($i = $this->max_rotation_level; $i >= 0; $i--) {
			$file_path = $this->composeFilePath($i);
			if (file_exists($file_path)) {
				if ($i === $this->max_rotation_level) {
					FS::rmFile($file_path);
				} else {
					$prev_i = $i + 1;
					$new_file_path = $this->composeFilePath($prev_i);
					rename($file_path, $new_file_path);
				}
			}
		}
	}

	protected function fileEligible(int $number = 0): bool {
		$file_path = $this->composeFilePath($number);
		if (file_exists($file_path)) {
			$size = filesize($file_path);
			if ($size !== false && $size >= $this->max_file_size) {
				return false;
			}
		}

		return true;
	}

	public static function getFileLineContent(string $file_path, int $from, ?int $to = null): null|string|array {
		if (file_exists($file_path)) {
			$fd = fopen($file_path, 'r');
			if ($fd) {
				$i = 0;
				$res = [];
				while (($line = fgets($fd)) !== false) {
					if ($from <= $i) {
						if (empty($to)) {
							return $line;
						} else {
							$res[$i] = $line;
							if ($i >= $to)
								return $res;
						}
					}
					$i++;
				}
				return $res;
			}
		}

		return null;
	}


}
