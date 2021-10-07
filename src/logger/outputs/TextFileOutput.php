<?php


namespace spaf\simputils\logger\outputs;


class TextFileOutput extends BasicFileOutput {

	public function getStorage(): string {
		return $this->storage;
	}

	public function getFileName(int $number = 0): string {
		return "{$this->file_name_prefix}{$number}.{$this->file_name_ext}";
	}

	public function log($msg, $priority = null) {
		$this->addToFile($msg);
	}
}