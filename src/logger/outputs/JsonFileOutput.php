<?php


namespace spaf\simputils\logger\outputs;


use spaf\simputils\logger\Logger;
use spaf\simputils\PHP;
use function json_decode;
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
		$content = PHP::getFileContent($path);
		$json_content = [];
		if (!empty($content)) {
			$json_content = json_decode($content, true);
		}
		$json_content[] = $msg;

		$this->saveFile(json_encode($json_content));
	}
}