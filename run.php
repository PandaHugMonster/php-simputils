<?php

use spaf\simputils\logger\Logger;
use spaf\simputils\logger\outputs\ContextOutput;
use spaf\simputils\logger\outputs\CsvFileOutput;
use spaf\simputils\logger\outputs\SyslogTransportOutput;
use spaf\simputils\logger\outputs\TextFileOutput;
use spaf\simputils\Settings;
use spaf\simputils\SimpleObject;

require_once 'vendor/autoload.php';


use function spaf\simputils\basic\pd;

//Settings::set_simple_object_type_case(Settings::SO_CAMEL_CASE);

//$now = DateTimeHelper::now();
//$str_dt = $now->format(DateTimeHelper::FMT_DATETIME_FULL);
//$dt_1 = DateTimeHelper::normalize($now);
//$dt_2 = DateTimeHelper::stringify(strtotime($str_dt));
//
//pd($now, $str_dt, $dt_1, $dt_2);

//Logger::get_default()->log_level = Logger::LEVEL_DEBUG;
//Logger::get_default()->log_level = Logger::LEVEL_CRITICAL;

$csv_output = new CsvFileOutput();

Logger::$default = new Logger('default', [
//	new SyslogTransportOutput(),
//	new TextFileOutput(),
	new ContextOutput(),
	$csv_output
]);
Logger::$format = "[%(asctime)s] (%(levelname)s) %(filename)s:%(lineno)d: %(message)s";

/**
 * Class MyObj
 * @property $hidden_field
 */
class MyObj extends SimpleObject {
	protected string $_hidden_field = 'toss';

	protected function get_hidden_field(): string {
		Logger::info('GETTING / Test "%s" Test "%d"', 'My check', 22);
		return $this->_hidden_field;
	}
	protected function set_hidden_field(string $val) {
		$this->_hidden_field = $val;
		Logger::info('SETTING / Test "%s" Test "%d"', 'My check', 22);
	}
}


$mo = new MyObj();
$mo->hidden_field = 112;

Logger::log('Test "%s" Test "%d"', 'My check', 22);

Logger::error($mo->hidden_field);
Logger::debug('TEST');
Logger::critical(Settings::version());