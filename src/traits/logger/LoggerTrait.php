<?php


namespace spaf\simputils\traits\logger;


use spaf\simputils\logger\Logger;
use spaf\simputils\logger\outputs\BasicOutput;
use spaf\simputils\logger\outputs\ContextOutput;

trait LoggerTrait {

	public function __construct(string $name = null, array $outputs = []) {
		$this->init($name, $outputs);
	}

	public function init(?string $name = null, array $outputs = []) {
		if (!empty($name))
			$this->name = $name;

		if (empty($outputs)) {
			$outputs = [
				new ContextOutput(),
			];
		}
		$this->outputs = $outputs;
	}

	public static function process_template_str(string $msg, array $values): string {
		return sprintf($msg, ...$values);
	}

	protected static function _sub_log(?int $level, string $msg, mixed ...$values) {
		/** @var Logger $logger */
		if (empty($logger))
			$logger = static::get_default();

		if (!empty($logger->outputs) && $level >= $logger->get_log_level()) {
			foreach ($logger->outputs as $output) {
				if ($output instanceof BasicOutput) {
					$msg = static::process_template_str($msg, $values);
					$data = $output->prepare_data($logger, $msg, $level, 3);
					$output->log_from_data($data, static::$format);
				}
			}
		}
	}

	public static function log(string $msg, mixed ...$values) {
		static::_sub_log(static::$default_logging_level, $msg, ...$values);
	}

	public static function critical(string $msg, mixed ...$values) {
		static::_sub_log(static::LEVEL_CRITICAL, $msg, ...$values);
	}

	public static function error(string $msg, mixed ...$values) {
		static::_sub_log(static::LEVEL_ERROR, $msg, ...$values);
	}

	public static function warning(string $msg, mixed ...$values) {
		static::_sub_log(static::LEVEL_WARNING, $msg, ...$values);
	}

	public static function info(string $msg, mixed ...$values) {
		static::_sub_log(static::LEVEL_INFO, $msg, ...$values);
	}

	public static function debug(string $msg, mixed ...$values) {
		static::_sub_log(static::LEVEL_DEBUG, $msg, ...$values);
	}

	public static function get_default() {
		if (empty(static::$default))
			static::$default = new static();
		return static::$default;
	}

	public function get_log_level(): int {
		return $this->log_level;
	}

	public function log_level_name(int $log_level): string {
		return match ($log_level) {
			static::LEVEL_CRITICAL => 'CRITICAL',
			static::LEVEL_ERROR => 'ERROR',
			static::LEVEL_WARNING => 'WARNING',
			static::LEVEL_INFO => 'INFO',
			static::LEVEL_DEBUG => 'DEBUG',
			static::LEVEL_NOT_SET => 'NOT SET',
		};
	}

	public function set_log_level(int $value) {
		$this->log_level = $value;
	}
}