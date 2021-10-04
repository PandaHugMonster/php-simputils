<?php


namespace spaf\simputils\traits\logger;


use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\logger\Logger;

trait LoggerBasicOutputTrait {

	abstract public function log($msg, $priority = null);

	public static function get_array_of_keys(): array {
		return [
			Logger::TEMPLATE_NAME, Logger::TEMPLATE_FILE_NAME, Logger::TEMPLATE_LINE_NUMBER,
			Logger::TEMPLATE_CREATED_TIME, Logger::TEMPLATE_FUNCTION_NAME, Logger::TEMPLATE_HUMAN_TIME,
			Logger::TEMPLATE_LEVEL_NUMBER, Logger::TEMPLATE_LEVEL_NAME, Logger::TEMPLATE_MESSAGE,
		];
	}
	public static function get_array_of_names(): array {
		return [
			Logger::TEMPLATE_NAME => 'logger_name',
			Logger::TEMPLATE_FILE_NAME => 'exec_file_name',
			Logger::TEMPLATE_LINE_NUMBER => 'exec_line_number',
			Logger::TEMPLATE_CREATED_TIME => 'ts',
			Logger::TEMPLATE_FUNCTION_NAME => 'exec_function_name',
			Logger::TEMPLATE_HUMAN_TIME => 'ts_human',
			Logger::TEMPLATE_LEVEL_NUMBER => 'priority_level_number',
			Logger::TEMPLATE_LEVEL_NAME => 'priority_level_name',
			Logger::TEMPLATE_MESSAGE => 'message',
		];
	}

	public function log_from_data($data, $template) {
		$this->log(static::format_final_res($data, $template), $data[Logger::TEMPLATE_LEVEL_NUMBER]);
	}

	public static function prepare_data(Logger $logger, string $msg, int $level, int $backtrace_level): array {
		$bt = debug_backtrace(limit: $backtrace_level + 1);
		$backtrace_level = $backtrace_level < 0?0:$backtrace_level;

		$caller = array_shift($bt);
		if ($backtrace_level > 0)
			for ($i = 0; $i < $backtrace_level; $i++) {
				$caller = array_shift($bt);
			}

		/** @noinspection PhpUnhandledExceptionInspection */
		$now = DateTimeHelper::normalize('now');

		$data = [
			$logger::TEMPLATE_NAME => $logger->name,
			$logger::TEMPLATE_FILE_NAME => !empty($caller['file'])?$caller['file']:null,
			$logger::TEMPLATE_LINE_NUMBER => !empty($caller['line'])?$caller['line']:null,
			$logger::TEMPLATE_CREATED_TIME => $now->getTimestamp(),
			$logger::TEMPLATE_FUNCTION_NAME => !empty($caller['function'])?$caller['function']:null,
			$logger::TEMPLATE_HUMAN_TIME => $now->format($logger->dt_format),
			$logger::TEMPLATE_LEVEL_NUMBER => $level,
			$logger::TEMPLATE_LEVEL_NAME => $logger->log_level_name($level),
			$logger::TEMPLATE_MESSAGE => $msg,
		];

		return $data;
	}

	protected static function preprocess_value($key, $value) {
		return $value;
	}

	public static function format_final_res($data, string $template, ?array $template_names = null): string {
		$res = $template;
		$template_names = empty($template_names)?static::get_array_of_keys():$template_names;

		foreach ($template_names as $key) {
			$val = isset($data[$key])?static::preprocess_value($key, $data[$key]):'';
			$res = str_replace($key, $val, $res);
		}
		return $res;
	}
}