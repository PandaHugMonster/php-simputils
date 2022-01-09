<?php


namespace spaf\simputils\logger\outputs;


use spaf\simputils\FS;
use spaf\simputils\logger\Logger;
use function json_encode;

class JsonFileOutput extends TextFileOutput {

	public string $file_name_ext = 'json';

	public function checkFileCorrectness($keys) {
		if (!$this->fileEligible()) {
			$this->rotateFiles();
		}
	}

	public function logFromData($data, $template) {
		$template = '';
		$keys = static::getArrayOfKeys();
		sort($keys);

		$this->checkFileCorrectness($keys);
		$res = [];
		$key_names = static::getArrayOfNames();
		foreach ($data as $key => $val) {
			$res[$key_names[$key]] = $val;
		}
		$this->log($res, $data[Logger::TEMPLATE_LEVEL_NUMBER]);
	}

	public function log($msg, $priority = null) {
		$path = $this->composeFilePath();
		$content = FS::file($path)->content ?? [];
		$content[] = $msg;

		// TODO Most likely outdated code, must be refactored with
		//      a newly created `File` infrastructure.
		$this->saveFile(json_encode($content));
	}
}
