<?php


namespace spaf\simputils\traits\logger;


use spaf\simputils\attributes\Property;
use spaf\simputils\logger\Logger;
use spaf\simputils\logger\outputs\BasicOutput;
use spaf\simputils\logger\outputs\ContextOutput;
use spaf\simputils\models\InitConfig;
use spaf\simputils\PHP;
use spaf\simputils\special\CodeBlocksCacheIndex;

trait LoggerTrait {

	public function __construct(string $name = null, array $outputs = []) {
		$this->init($name, $outputs);
	}

	public function init(?string $name = null, array $outputs = []) {
		if (!empty($name)) {
			$this->name = $name;
		} else {
			$init_config = PHP::getInitConfig();
			$default_name = 'default';
			$this->name = !empty($init_config->name)
				?($default_name.'-'.$init_config->name)
				:$default_name;
		}

		if (empty($outputs)) {
			$outputs = [
				new ContextOutput(),
			];
		}
		$this->outputs = $outputs;
	}

	public static function processTemplateStr(string $msg, array $values): string {
		return sprintf($msg, ...$values);
	}

	protected static function _subLog(?int $level, string $msg, mixed ...$values) {
		/** @var Logger $logger */
		if (empty($logger))
			$logger = static::getDefault();

		if (!empty($logger->outputs) && $level >= $logger->getLogLevel()) {
			foreach ($logger->outputs as $output) {
				if ($output instanceof BasicOutput) {
					$msg = static::processTemplateStr($msg, $values);
					$data = $output->prepareData($logger, $msg, $level, 3);
					$output->logFromData($data, static::$format);
				}
			}
		}
	}

	public static function log(string $msg, mixed ...$values) {
		static::_subLog(static::$default_logging_level, $msg, ...$values);
	}

	public static function critical(string $msg, mixed ...$values) {
		static::_subLog(static::LEVEL_CRITICAL, $msg, ...$values);
	}

	public static function error(string $msg, mixed ...$values) {
		static::_subLog(static::LEVEL_ERROR, $msg, ...$values);
	}

	public static function warning(string $msg, mixed ...$values) {
		static::_subLog(static::LEVEL_WARNING, $msg, ...$values);
	}

	public static function info(string $msg, mixed ...$values) {
		static::_subLog(static::LEVEL_INFO, $msg, ...$values);
	}

	public static function debug(string $msg, mixed ...$values) {
		static::_subLog(static::LEVEL_DEBUG, $msg, ...$values);
	}

	public static function getDefault(): static {
		$class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_LOGGER,
			static::class
		);
		if (empty(static::$default))
			static::$default = new $class();
		return static::$default;
	}

	#[Property('logLevel')]
	public function getLogLevel(): int {
		return $this->log_level;
	}

	#[Property('logLevel')]
	public function setLogLevel(int $value) {
		$this->log_level = $value;
	}

	public function logLevelName(int $log_level): string {
		return match ($log_level) {
			static::LEVEL_CRITICAL => 'CRITICAL',
			static::LEVEL_ERROR => 'ERROR',
			static::LEVEL_WARNING => 'WARNING',
			static::LEVEL_INFO => 'INFO',
			static::LEVEL_DEBUG => 'DEBUG',
			static::LEVEL_NOT_SET => 'NOT SET',
		};
	}
}
