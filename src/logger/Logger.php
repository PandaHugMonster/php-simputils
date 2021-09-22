<?php


namespace spaf\simputils\logger;

use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\interfaces\LoggerInterface;
use spaf\simputils\SimpleObject;
use spaf\simputils\traits\logger\LoggerTrait;

class Logger extends SimpleObject implements LoggerInterface {
	use LoggerTrait;

	public string $name = 'default';

	protected array $outputs = [];
	public ?int $log_level = LoggerInterface::LEVEL_INFO;
	public ?string $dt_format = DateTimeHelper::FMT_DATETIME_FULL;

	public static mixed $default = null;
	public static int $default_logging_level = LoggerInterface::LEVEL_INFO;
	public static string $format = "[%(asctime)s] (%(levelname)s) %(filename)s:%(lineno)d | %(funcname)s():\t %(message)s";

}