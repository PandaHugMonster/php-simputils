<?php


use spaf\simputils\attributes\Deprecated;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\helpers\DateTimeHelper;
use spaf\simputils\models\Version;
use spaf\simputils\traits\PropertiesTrait;
use function spaf\simputils\basic\pd;

require_once 'vendor/autoload.php';

/**
 * @property-read string $a1
 * @property string $a2
 * @property string $a3
 */
class A extends SimpleObject {

	protected $_a2;
	protected $_a3;
	public $a4 = 'toto';

	public static $properties = ['dodododo'];

	#[Deprecated('Because I said so', 'Use nothing, ok?!')]
	public function test() {
		return 'test';
	}

	#[Property]
	protected function a1(): string {
		return date(DateTimeHelper::FMT_DATETIME_FULL);
	}

	#[Property]
	protected function a2($var, $type): mixed {
		return $type == Property::TYPE_SET
			?$this->_test = $var
			:$this->_test;
	}

	#[Property('a3')]
	protected function setDododo($var) {
		$this->_a3 = $var;
	}

	#[Property('a3')]
	protected function getDododo(): mixed {
		return $this->_a3;
	}
}


class B extends A {

}

class C {
	use PropertiesTrait;

}

class F {
	use PropertiesTrait;

}

//$v = Settings::version();
$attrs = [];

$a = new A(a1: 'test 1', a2: 2, a3: true);
$z = new A(a1: 'goo', a2: 10, a3: false);
$b = new B('one');
$c = new C('two');
$f = new F('three');

pd(
	$a->___cached_property_call_points,
	$b->___cached_property_call_points,
	$c->___cached_property_call_points,
	$f->___cached_property_call_points,
);
pd($a::$___cached_property_call_points);

$max_size = 1_000_000;

$t1 = microtime(true);
for ($i = 0; $i < $max_size; $i++) {
	$v = $a->test();
}

$t2 = microtime(true);
for ($i = 0; $i < $max_size; $i++) {
	$v = $a->a4;
}

$t3 = microtime(true);

pd([
	"Pseudo" => round($t2 - $t1, 3),
	"Real  " => round($t3 - $t2, 3),
]);
// NOTE Reading test:
//      500_000 Times
//          [Pseudo] => 10.551
//          [Real  ] => 0.037

// NOTE Reading from function test:
//      500_000 Times
//          [Pseudo] => 0.14
//          [Real  ] => 0.036
//      1_000_000 Times
//          [Pseudo] => 0.43
//          [Real  ] => 0.111

// NOTE Reading from local sub var test:
//      500_000 Times
//          [Pseudo] => 0.042 (weird, but local var works slower than the direct copy)
//          [Real  ] => 0.039
//      1_000_000 Times
//          [Pseudo] => 0.086 (weird, but local var works slower than the direct copy)
//          [Real  ] => 0.081

//$version = new Version('13.34.56', 'My app');
//echo "{$version} / type: {$version->obj_type}\n";
// Outputs like (as a str): 13.34.56 / type: spaf\simputils\models\Version
//
//print_r($version);
// Outputs like (as a str):
//      spaf\simputils\models\Version Object
//      (
//          [software_name] => My app
//          [parsed_version] => 13.34.56
//      )

$version = new Version('My APP RC13.34.56', 'My app');
echo "$version\n";
// Outputs like (as a str): 13.34.56-RC

$version = new Version('My APP 13.34.56RC', 'My app');
echo "$version\n";
// Outputs like (as a str): 13.34.56-RC

$version = new Version(' 13.34.56RC55 ', 'My app');
echo "$version\n";
// Outputs like (as a str): 13.34.56-RC55

$version = new Version('13.34.56F99', 'My app');
echo "$version\n";
// Outputs like (as a str): 13.34.56

$version = new Version('13.34.56F99', 'My app');
echo "$version\n";
// Outputs like (as a str): 13.34.56

$version = new Version('20020611', 'My app');
echo "$version\n";
// Outputs like (as a str): 20020611.0.0

$version = new Version('15', 'My app');
echo "$version\n";
// Outputs like (as a str): 15.0.0

$version = new Version('SOMERUBBISHHERE--15.12.0');
echo "$version\n";
// Outputs like (as a str): 15.12.0



//PHP::$use_box_instead_of_array = false;


//$a = PHP::version();
//$b = $a->toArray();


//echo "\$a is {$a->obj_type}\nand\n\$b is {$b->obj_type}";
die;

////$phpi = PHP::info();
////
////$res = $phpi->slice(['php_version']);
//
//
////$a = box(['Val1', 'HHH', 15, 99]);
////$b = $a->slice(by_ref: true);
////$t = [];
////$phpi = box(['Val1', 'HHH', 15, 99]);
////$res = $phpi->slice(1, 3, by_ref: true);
////$phpi[1] = 666;
//
//pd("My text line {$res}");
//
//pd("\n\nend");
//
//$before = memory_get_usage();
//
//$main = [];
//
//// Arrays   65.76MB
////          Time 0.193 sec
//
//// Exp 2:   65.93MB
////          Time 0.211 sec
//
//// Exp 3:   65.89MB (slightly slower when object instead of array directly)
////          Time 0.887 sec
//
//
////
//
//$st = microtime(true);
//
//for ($i = 0; $i < 1000; $i++) {
////	$a = new Box();
//	$a = [];
////	$a = new Exp();
//	for ($k = 0; $k < 1000; $k++) {
//		$a[$k] = "D: {$k}";
//	}
//	$main[] = $a;
//}PHP::
//
//$st_end = microtime(true);
//
//$time_res = round($st_end - $st, 3);
//
//$res = memory_get_usage() - $before;
//
//pd("Time {$time_res} sec\n\n", DataHelper::humanReadable($res));
//
////Logger::$default = new Logger('default', [
////	$a = new ContextOutput(),
////	$b = new CsvFileOutput()
////]);
////
////
//////pd(is_a(ContextOutput::class, ContextOutput::class));
//////pd(PHP::classContains($b::class, BasicOutput::class));
////
////class A {}
////class B extends A {}
////class C {}
////
////$b_contains_a = PHP::classContains(B::class, A::class);
////// Returns true, because B class is extended from A
////
////$a_contains_c = PHP::classContains(A::class, C::class);
////
////// Returns false, Because A class is not C class and not having C class as one of it's
////// parents
////
////$a_contains_b = PHP::classContains(A::class, B::class);
////// Returns false, Because A class is independent from B class (B extended from A,
////// not vice-versa)
////
////$c_contains_c = PHP::classContains(C::class, C::class);
////// Returns true, Because C class is C class
////
//////$sf = new DefaultSystemFingerprint();
//////$phpi = PHP::info();
////
//////pd(PHP::isArrayCompatible($phpi));
////
//////foreach ($phpi as $k => $v) {
//////	pd("/KKK $k KKK/", "||  VVV $v VVV ||");
//////}
////
//////echo $sf; die;
////
//////function handler($err_no, $err_str, $err_file, $err_line) {
//////	Logger::error('Error happened "%s" OMG', $err_str);
//////	die;
//////}
////
//////error_reporting(E_ALL);
////
//////set_error_handler('handler');
//////set_exception_handler('handler');
////
////###
////
////
////Logger::log('## App started');
////
//////trigger_error('Error notice');
//////$git_info = new GitInfo(__DIR__);
////
//////Logger::log(DataHelper::human_readable('1200000kb'));
//////Logger::log("PHP version: %s", SystemHelper::php_version());
//////Logger::log("OS: %s", SystemHelper::os());
//////Logger::log("UNAME: %s", SystemHelper::uname());
//////Logger::log("Architecture: %s", SystemHelper::architecture());
//////Logger::log("GIT Commit id: %s", $git_info->get_commit_id());
//////Logger::log("GIT Commit id (short): %s", $git_info->get_commit_id_short());
//////Logger::log("GIT Author name: %s", $git_info->get_author_name());
//////Logger::log("GIT Author email: %s", $git_info->get_author_email());
//////Logger::log("GIT Author date-time: %s", $git_info->get_author_datetime());
//////Logger::log("GIT Subject: %s", $git_info->get_commit_subject());
//////Logger::log("GIT Body: %s", $git_info->get_commit_body());
////
//////pd(SystemHelper::php_info()['extensions']);
//////pd(Version::wrap('20040000', false));
//////pd(Version::wrap('20040000', false));
//////pd(Version::wrap('20040000'));
//////pd(Version::wrap('2'));
//////pd(Version::wrap(15));
//////pd(Version::wrap('12.2'));
//////pd(Version::wrap('CorelDraw 12'));
//////pd(Version::wrap('12'));
//////pd(Version::wrap('12.12'));
//////pd(Version::wrap('12.12.12rc'));
//////pd(Version::wrap('12.34.56RC78-99-GF66AA22'));
//////pd(Version::wrap('WarHold 12.34.56-rC1', 'warHOLD'));
//////pd(Version::wrap('WarHold 12.34.56-dev', 'warHOLD'));
//////pd(Version::wrap('WarHoldDEV 12.34.56', 'warHOLDDEv'));
//////pd(Version::wrap('WarHoldDEVJJJJJJdevp--12.34.56rc455TTTTT TTTT', 'warHOLDDEv'));
//////$a1 = new Version('DEVJJJJJJdevp--13.34.56pTTTTT TTTT WarHold', 'warHOLD');
//////$a2 = Version::wrap('DEVJJJJJJdevp--13.34.56#TTTTT TTTT WarHold', 'warHOLD');
//////pd([$a1->e($a2), $a1, $a2]);
//////pd(serialize());
////
////// TODO SOMERUBBISHHERE--15.12 is failing in being parsed
////
//////$version = new Version('13.34.56', 'My app');
//////$version = new Version('SOMERUBBISHHERE--15.12.0');
//////
//////echo $version;die;
////
//////pd(PHP::classUsesTrait(Version::class, MetaMagic::class));
////
//////$settings = new Settings();
////
//////PHP::$serialization_mechanism = PHP::SERIALIZATION_TYPE_PHP;
//////$a1->build_type = 'RC';
//////$a1->build_revision = 8;
//////$res = \serialize($a1);
//////$res = \unserialize($res);
//////$res = PHP::deserialize($res);
//////$res = PHP::serialize($settings);
//////$res = $settings;
//////$res = PHP::serialize($settings);
//////$res = \unserialize($res);
////
//////Settings::redefine_pd(function (...$args) {
//////	echo "BOK\n\n";
//////	print_r($args);
//////	echo "\n\nBOK END";
//////
//////	die;
//////});
//////Settings::redefine_pd(function (...$args) {
//////	echo "BOK\n\n";
//////	print_r($args);
//////	echo "\n\nBOK END";
//////
//////	die;
//////});
////
////class CustomParser extends DefaultVersionParser {
////	// Your own custom parser
////}
////
//////Version::_metaMagic($a1, '___setup', ['major' => 100_500]);
//////$res->e($res);
////
////class MyVersionParser extends DefaultVersionParser {
////	public function toString(Version|string $obj) : string{
////		return "<{$obj->major}/{$obj->minor}/{$obj->patch}>";
////	}
////}
////
////$v1 = new Version('1.2.3-A2');
////$v2 = new Version('1.2.3');
////
//////pd(PHP::boolStr($v1->e($v2)));
////
//////$dir = '/tmp/simputils';
////
////$phpi1 = PHP::info();
////
//////pd($phpi1->kernel_name);
//////pd($phpi1->php_extension_build);
//////pd($phpi1->extra_ini_files);
////foreach ($phpi1->toArray() as $key => $val) {
////	Logger::info('Key: %s ; Type: %s ;', $key, PHP::type($val));
////}
//////pd("My PHP info item of  is {$phpi1->}");
//////pd(InternalMemoryCache::$original_phpinfo_string);
//////PHP::rmFile('/tmp/simputils/tests', true);
//////pd(PHP::listFiles($dir, true));
////
////// then in the entry point file:
////
//////$php_version = new Version(phpversion(), 'PHP');
//////
//////echo get_class($php_version->parser);
//////echo "Full version: {$php_version}\n";
//////echo "Type is: {$php_version->build_type}; Rev is: {$php_version->build_revision}\n";
//////echo "Major is: {$php_version->major}\n";
//////echo "Minor is: {$php_version->minor}\n";
//////echo "Patch is: {$php_version->patch}\n";
//////pd([$a1->gt($a2), 'a1' => $a1, 'a2' => $a2]);
////
////// TODO log to browser console log
////// TODO disabled printout context log in non CLI mode
