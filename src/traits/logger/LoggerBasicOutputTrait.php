<?php


namespace spaf\simputils\traits\logger;


use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\logger\Logger;

trait LoggerBasicOutputTrait {

	abstract public function log($msg, $priority = null);

	public static function getArrayOfKeys(): array {
		return [
			Logger::TEMPLATE_NAME, Logger::TEMPLATE_FILE_NAME, Logger::TEMPLATE_LINE_NUMBER,
			Logger::TEMPLATE_CREATED_TIME, Logger::TEMPLATE_FUNCTION_NAME, Logger::TEMPLATE_HUMAN_TIME,
			Logger::TEMPLATE_LEVEL_NUMBER, Logger::TEMPLATE_LEVEL_NAME, Logger::TEMPLATE_MESSAGE,
		];
	}
	public static function getArrayOfNames(): array {
		return [
			Logger::TEMPLATE_NAME => 'logger_name', // @codeCoverageIgnore
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

	public function logFromData($data, $template) {
		$this->log(static::formatFinalRes($data, $template), $data[Logger::TEMPLATE_LEVEL_NUMBER]);
	}

	public static function prepareData(Logger $logger, string $msg, int $level, int $backtrace_level): array {
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
			$logger::TEMPLATE_LEVEL_NAME => $logger->logLevelName($level),
			$logger::TEMPLATE_MESSAGE => $msg,
		];

		return $data;
	}

	protected static function preprocessValue($key, $value) {
		return $value;
	}

	public static function formatFinalRes($data, string $template, ?array $template_names = null): string {
		$res = $template;
		$template_names = empty($template_names)?static::getArrayOfKeys():$template_names;

		foreach ($template_names as $key) {
			$val = isset($data[$key])?static::preprocessValue($key, $data[$key]):'';
			$res = str_replace($key, $val, $res);
		}
		return $res;
	}
}