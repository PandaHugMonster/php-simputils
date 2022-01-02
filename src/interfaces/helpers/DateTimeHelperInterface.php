<?php


namespace spaf\simputils\interfaces\helpers;


interface DateTimeHelperInterface {
	const FMT_DATE = 'Y-m-d';
	const FMT_TIME = 'H:i:s';
	const FMT_DATETIME = self::FMT_DATE.' '.self::FMT_TIME;
	const FMT_DATETIME_FULL = self::FMT_DATETIME.'.u';
	const FMT_STRINGIFY_DEFAULT = self::FMT_DATETIME_FULL;
}