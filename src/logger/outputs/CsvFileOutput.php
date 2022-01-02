<?php


namespace spaf\simputils\logger\outputs;


use spaf\simputils\FS;
use spaf\simputils\logger\Logger;
use function file_exists;

class CsvFileOutput extends TextFileOutput {

	public string $file_name_ext = 'csv';
	public string $separator = ',';
	public string $quotation_mark = '"';
	public bool $check_header_conformity = true;
	public bool $headless = false;

	protected static function preprocessValue($key, $value) {
		return addslashes($value);
	}

	public function checkFileCorrectness($keys) {
		$file_path = $this->composeFilePath();
		$ineligible = false;

		$keys_names = $this->getArrayOfNames();
		$header = '';
		foreach ($keys as $key) {
			$separator = !empty($header)?$this->separator:'';
			$header .= $separator.$this->quotation_mark.$keys_names[$key].$this->quotation_mark;
		}

		if ($this->check_header_conformity) {
			$res = $this->getFileLineContent($file_path, 0);
			if (!empty($res) && preg_replace('/\s*/', '', $header) !== preg_replace('/\s*/', '', $res)) {
				$ineligible = true;
			}
		}

		if (!$this->fileEligible() || $ineligible) {
			$this->rotateFiles();
		}

		if (!file_exists($file_path)) {
			if (!$this->headless) {
				$this->prepareStorage($file_path);
				FS::mkFile($file_path, "$header\n");
			}
		}
	}

	public function logFromData($data, $template) {
		$template = '';
		$keys = static::getArrayOfKeys();
		sort($keys);

		$this->checkFileCorrectness($keys);

		foreach ($keys as $key) {
			$separator = !empty($template)?$this->separator:'';
			$template .= $separator.$this->quotation_mark.$key.$this->quotation_mark;
		}

		$this->log(static::formatFinalRes($data, $template, $keys), $data[Logger::TEMPLATE_LEVEL_NUMBER]);
	}
}
