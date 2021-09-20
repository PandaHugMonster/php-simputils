<?php


namespace spaf\simputils\logger;

use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\interfaces\helpers\LoggerInterface;
use spaf\simputils\SimpleObject;
use spaf\simputils\traits\LoggerTrait;

class Logger extends SimpleObject implements LoggerInterface {
	use LoggerTrait;

	public string $name = 'default-logger';

	protected array $outputs = [];
	public ?int $log_level = LoggerInterface::LEVEL_INFO;
	public ?string $dt_format = DateTimeHelper::FMT_DATETIME_FULL;

	public static $default = null;
	public static $format = "[%(asctime)s] (%(levelname)s) %(filename)s:%(lineno)d | %(funcname)s():\t %(message)s";

}