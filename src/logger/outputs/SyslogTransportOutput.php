<?php


namespace spaf\simputils\logger\outputs;


use spaf\simputils\interfaces\LoggerInterface;
use spaf\simputils\logger\Logger;

class SyslogTransportOutput extends BasicOutput {

	const LOG_EMERG = LOG_EMERG;
	const LOG_ALERT = LOG_ALERT;
	const LOG_CRIT = LOG_CRIT;
	const LOG_ERR = LOG_ERR;
	const LOG_WARNING = LOG_WARNING;
	const LOG_NOTICE = LOG_NOTICE;
	const LOG_INFO = LOG_INFO;
	const LOG_DEBUG = LOG_DEBUG;

	public ?string $template = "(%(levelname)s) %(filename)s:%(lineno)d | %(funcname)s(): %(message)s";

	public function __construct(?string $template = null) {
		$this->template = empty($template)?$this->template:$template;
	}

	public function log($msg, $priority = null) {
		$priority = static::map_logger_level_to_syslog_level($priority);
		if (is_null($priority))
			$priority = static::LOG_INFO;
		syslog($priority, $msg);
	}

	public function log_from_data($data, $template) {
		$template = !empty($this->template)?$this->template:$template;
		$this->log(static::format_final_res($data, $template), $data[Logger::TEMPLATE_LEVEL_NUMBER]);
	}

	public static function map_logger_level_to_syslog_level($logger_level): int {
		return match ($logger_level) {
			LoggerInterface::LEVEL_CRITICAL => static::LOG_CRIT,
			LoggerInterface::LEVEL_ERROR => static::LOG_ERR,
			LoggerInterface::LEVEL_WARNING => static::LOG_WARNING,
			LoggerInterface::LEVEL_INFO => static::LOG_INFO,
			LoggerInterface::LEVEL_DEBUG => static::LOG_DEBUG,
		};
	}

	public static function map_syslog_level_to_logger_level($syslog_level): int {
		return match ($syslog_level) {
			static::LOG_EMERG, static::LOG_ALERT, static::LOG_CRIT => LoggerInterface::LEVEL_CRITICAL,
			static::LOG_ERR => LoggerInterface::LEVEL_ERROR,
			static::LOG_WARNING => LoggerInterface::LEVEL_WARNING,
			static::LOG_NOTICE, static::LOG_INFO => LoggerInterface::LEVEL_INFO,
			static::LOG_DEBUG => LoggerInterface::LEVEL_DEBUG,
		};
	}

}