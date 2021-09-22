<?php


namespace spaf\simputils\logger\outputs;


use spaf\simputils\logger\Logger;

class CsvFileOutput extends TextFileOutput {

	public string $file_name_ext = 'csv';
	public string $separator = ',';
	public string $quotation_mark = '"';
	public bool $check_header_conformity = true;
	public bool $headless = false;

	protected static function preprocess_value($key, $value) {
		return addslashes($value);
	}

	public function check_file_correctness($keys) {
		$file_path = $this->compose_file_path();
		$ineligible = false;

		$keys_names = $this->get_array_of_names();
		$header = '';
		foreach ($keys as $key) {
			$separator = !empty($header)?$this->separator:'';
			$header .= $separator.$this->quotation_mark.$keys_names[$key].$this->quotation_mark;
		}

		if ($this->check_header_conformity) {
			$res = $this->get_file_line_content($file_path, 0);
			if (!empty($res) && preg_replace('/\s*/', '', $header) !== preg_replace('/\s*/', '', $res)) {
				$ineligible = true;
			}
		}

		if (!$this->file_eligible() || $ineligible) {
			$this->rotate_files();
		}

		if (!file_exists($file_path)) {
			if (!$this->headless) {
				file_put_contents($file_path, "$header\n");
			}
		}
	}

	public function log_from_data($data, $template) {
		$template = '';
		$keys = static::get_array_of_keys();
		sort($keys);

		$this->check_file_correctness($keys);

		foreach ($keys as $key) {
			$separator = !empty($template)?$this->separator:'';
			$template .= $separator.$this->quotation_mark.$key.$this->quotation_mark;
		}

		$this->log(static::format_final_res($data, $template, $keys), $data[Logger::TEMPLATE_LEVEL_NUMBER]);
	}
}