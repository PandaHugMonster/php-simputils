<?php


namespace spaf\simputils\traits\logger;


trait LoggerBasicFileOutputTrait {

	abstract public function get_storage(): string;
	abstract public function get_file_name(int $number = 0): string;

	public function __construct(?string $storage = null, ?string $prefix = null, ?string $ext = null) {
		if (!empty($storage))
			$this->storage = $storage;
		if (!empty($prefix))
			$this->file_name_prefix = $prefix;
		if (!empty($ext))
			$this->file_name_ext = $ext;
	}

	public function save_file(string $data_str) {
		$path = $this->compose_file_path();
		$this->prepare_storage($path);

		file_put_contents($path, $data_str);
	}

	public function add_to_file(string $data_str) {
		$path = $this->compose_file_path();
		$this->prepare_storage($path);

		$fd = fopen($path, 'a');
		fwrite($fd, "{$data_str}\n");
		fclose($fd);
		if ($this->clear_file_stat_cache)
			clearstatcache();
	}

	protected function compose_file_path(int $number = 0): string {
		$storage = $this->get_storage();
		$file_name = $this->get_file_name($number);
		$path = "{$storage}/{$file_name}";
		return $path;
	}

	protected function prepare_storage($path) {
		if ($this->is_structure_auto_created) {
			$basedir = dirname($path);
			if (!file_exists($basedir)) {
				mkdir($basedir, recursive: true);
			}
		}
	}

	protected function rotate_files() {
		for ($i = $this->max_rotation_level; $i >= 0; $i--) {
			$file_path = $this->compose_file_path($i);
			if (file_exists($file_path)) {
				if ($i === $this->max_rotation_level) {
					unlink($file_path);
				} else {
					$prev_i = $i + 1;
					$new_file_path = $this->compose_file_path($prev_i);
					rename($file_path, $new_file_path);
				}
			}
		}
	}

	protected function file_eligible(int $number = 0): bool {
		$file_path = $this->compose_file_path($number);
		if (file_exists($file_path)) {
			$size = filesize($file_path);
			if ($size !== false && $size >= $this->max_file_size) {
				return false;
			}
		}

		return true;
	}

	public static function get_file_line_content(string $file_path, int $from, ?int $to = null): null|string|array {
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