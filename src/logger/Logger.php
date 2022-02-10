<?php
/** @noinspection PhpHierarchyChecksInspection */


namespace spaf\simputils\logger;

use spaf\simputils\DT;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\LoggerInterface;
use spaf\simputils\models\InitConfig;
use spaf\simputils\traits\logger\LoggerTrait;
use spaf\simputils\traits\RedefinableComponentTrait;

class Logger extends SimpleObject implements LoggerInterface {
	use LoggerTrait;
	use RedefinableComponentTrait;

	public ?string $name = null;

	protected array $outputs = [];
	public ?int $log_level = LoggerInterface::LEVEL_INFO;
	public ?string $dt_format = DT::FMT_DATETIME_FULL;

	public static mixed $default = null;
	public static int $default_logging_level = LoggerInterface::LEVEL_INFO;
	public static string $format
		= "[%(asctime)s] (%(levelname)s) %(filename)s:%(lineno)d | %(funcname)s():\t %(message)s";

	public static function redefComponentName(): string {
		return InitConfig::REDEF_LOGGER;
	}
}
