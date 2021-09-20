<?php


namespace spaf\simputils\logger\outputs;


use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\logger\Logger;

abstract class BasicOutput {

	abstract public function log($msg);

	public function log_from_data($data, $template) {
		$this->log(static::format_final_res($data, $template));
	}

	public static function prepare_data(Logger $logger, string $msg, int $level, int $backtrace_level): array {
		$bt = debug_backtrace();
		$backtrace_level = $backtrace_level < 0?0:$backtrace_level;

		$caller = array_shift($bt);
		if ($backtrace_level > 0)
			for ($i = 0; $i < $backtrace_level; $i++) {
				$pre_caller = array_shift($bt);
				if (!empty($pre_caller))
					$caller = $pre_caller;
			}

		/** @noinspection PhpUnhandledExceptionInspection */
		$now = DateTimeHelper::normalize('now');

		$data = [
			$logger::TEMPLATE_NAME => $logger->name,
			$logger::TEMPLATE_FILE_NAME => $caller['file'],
			$logger::TEMPLATE_LINE_NUMBER => $caller['line'],
			$logger::TEMPLATE_CREATED_TIME => $now->getTimestamp(),
			$logger::TEMPLATE_FUNCTION_NAME => $caller['function'],
			$logger::TEMPLATE_HUMAN_TIME => $now->format($logger->dt_format),
			$logger::TEMPLATE_LEVEL_NUMBER => $level,
			$logger::TEMPLATE_LEVEL_NAME => $logger->log_level_name($level),
			$logger::TEMPLATE_MESSAGE => $msg,
		];

		return $data;
	}

	public static function format_final_res($data, string $template, ?array $template_names = null): string {
		$res = $template;
		$template_names = empty($template_names)?[
			Logger::TEMPLATE_NAME, Logger::TEMPLATE_FILE_NAME, Logger::TEMPLATE_LINE_NUMBER,
			Logger::TEMPLATE_CREATED_TIME, Logger::TEMPLATE_FUNCTION_NAME, Logger::TEMPLATE_HUMAN_TIME,
			Logger::TEMPLATE_LEVEL_NUMBER, Logger::TEMPLATE_LEVEL_NAME, Logger::TEMPLATE_MESSAGE,
		]:$template_names;

		foreach ($template_names as $key) {
			if (!empty($data[$key]))
				$res = str_replace($key, $data[$key], $res);
		}
		return $res;
	}

}