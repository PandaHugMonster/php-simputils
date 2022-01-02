<?php


namespace spaf\simputils\logger\outputs;


use spaf\simputils\traits\logger\LoggerBasicFileOutputTrait;

abstract class BasicFileOutput extends BasicOutput {
	use LoggerBasicFileOutputTrait;

	public bool $is_structure_auto_created = true;
	public string $storage = '/tmp/simputils/logs';
	public string $file_name_prefix = 'log-file-';
	public string $file_name_ext = 'log';
	public int $max_rotation_level = 8;
	public int $max_file_size = 1024 * 1024 * 8;
	public bool $clear_file_stat_cache = true;

}