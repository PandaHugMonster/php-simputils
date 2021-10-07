<?php

use spaf\simputils\logger\Logger;
use spaf\simputils\logger\outputs\ContextOutput;
use spaf\simputils\logger\outputs\CsvFileOutput;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\versions\DefaultVersionParser;
use function spaf\simputils\basic\pd;

require_once 'vendor/autoload.php';

Logger::$default = new Logger('default', [
	new ContextOutput(),
	new CsvFileOutput()
]);

//function handler($err_no, $err_str, $err_file, $err_line) {
//	Logger::error('Error happened "%s" OMG', $err_str);
//	die;
//}

//error_reporting(E_ALL);

//set_error_handler('handler');
//set_exception_handler('handler');

###


Logger::log('## App started');

//trigger_error('Error notice');
//$git_info = new GitInfo(__DIR__);

//Logger::log(DataHelper::human_readable('1200000kb'));
//Logger::log("PHP version: %s", SystemHelper::php_version());
//Logger::log("OS: %s", SystemHelper::os());
//Logger::log("UNAME: %s", SystemHelper::uname());
//Logger::log("Architecture: %s", SystemHelper::architecture());
//Logger::log("GIT Commit id: %s", $git_info->get_commit_id());
//Logger::log("GIT Commit id (short): %s", $git_info->get_commit_id_short());
//Logger::log("GIT Author name: %s", $git_info->get_author_name());
//Logger::log("GIT Author email: %s", $git_info->get_author_email());
//Logger::log("GIT Author date-time: %s", $git_info->get_author_datetime());
//Logger::log("GIT Subject: %s", $git_info->get_commit_subject());
//Logger::log("GIT Body: %s", $git_info->get_commit_body());

//pd(SystemHelper::php_info()['extensions']);
//pd(Version::wrap('20040000', false));
//pd(Version::wrap('20040000', false));
//pd(Version::wrap('20040000'));
//pd(Version::wrap('2'));
//pd(Version::wrap(15));
//pd(Version::wrap('12.2'));
//pd(Version::wrap('CorelDraw 12'));
//pd(Version::wrap('12'));
//pd(Version::wrap('12.12'));
//pd(Version::wrap('12.12.12rc'));
//pd(Version::wrap('12.34.56RC78-99-GF66AA22'));
//pd(Version::wrap('WarHold 12.34.56-rC1', 'warHOLD'));
//pd(Version::wrap('WarHold 12.34.56-dev', 'warHOLD'));
//pd(Version::wrap('WarHoldDEV 12.34.56', 'warHOLDDEv'));
//pd(Version::wrap('WarHoldDEVJJJJJJdevp--12.34.56rc455TTTTT TTTT', 'warHOLDDEv'));
//$a1 = new Version('DEVJJJJJJdevp--13.34.56pTTTTT TTTT WarHold', 'warHOLD');
//$a2 = Version::wrap('DEVJJJJJJdevp--13.34.56#TTTTT TTTT WarHold', 'warHOLD');
//pd([$a1->e($a2), $a1, $a2]);
//pd(serialize());

// TODO SOMERUBBISHHERE--15.12 is failing in being parsed

//$version = new Version('13.34.56', 'My app');
//$version = new Version('SOMERUBBISHHERE--15.12.0');
//
//echo $version;die;

//pd(PHP::classUsesTrait(Version::class, MetaMagic::class));

//$settings = new Settings();

//PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_PHP;
//$a1->build_type = 'RC';
//$a1->build_revision = 8;
//$res = \serialize($a1);
//$res = \unserialize($res);
//$res = PHP::deserialize($res);
//$res = PHP::serialize($settings);
//$res = $settings;
//$res = PHP::serialize($settings);
//$res = \unserialize($res);

//Settings::redefine_pd(function (...$args) {
//	echo "BOK\n\n";
//	print_r($args);
//	echo "\n\nBOK END";
//
//	die;
//});
//Settings::redefine_pd(function (...$args) {
//	echo "BOK\n\n";
//	print_r($args);
//	echo "\n\nBOK END";
//
//	die;
//});

class CustomParser extends DefaultVersionParser {
	// Your own custom parser
}

//Version::_metaMagic($a1, '___setup', ['major' => 100_500]);
//$res->e($res);

class MyVersionParser extends DefaultVersionParser {
	public function toString(Version|string $obj) : string{
		return "<{$obj->major}/{$obj->minor}/{$obj->patch}>";
	}
}

$v1 = new Version('1.2.3-A2');
$v2 = new Version('1.2.3');

pd(PHP::boolStr($v1->e($v2)));

// then in the entry point file:

//$php_version = new Version(phpversion(), 'PHP');
//
//echo get_class($php_version->parser);
//echo "Full version: {$php_version}\n";
//echo "Type is: {$php_version->build_type}; Rev is: {$php_version->build_revision}\n";
//echo "Major is: {$php_version->major}\n";
//echo "Minor is: {$php_version->minor}\n";
//echo "Patch is: {$php_version->patch}\n";
//pd([$a1->gt($a2), 'a1' => $a1, 'a2' => $a2]);

// TODO log to browser console log
// TODO disabled printout context log in non CLI mode
