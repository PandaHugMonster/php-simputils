<?php


namespace spaf\simputils\logger\outputs;


class TextFileOutput extends BasicFileOutput {

	public function get_storage(): string {
		return $this->storage;
	}

	public function get_file_name(int $number = 0): string {
		return "{$this->file_name_prefix}{$number}.{$this->file_name_ext}";
	}

	public function log($msg, $priority = null) {
		$this->add_to_file($msg);
	}
}