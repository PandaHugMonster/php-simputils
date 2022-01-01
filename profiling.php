<?php

use spaf\simputils\Data;
use spaf\simputils\logger\Logger;
use spaf\simputils\logger\outputs\ContextOutput;
use spaf\simputils\logger\outputs\CsvFileOutput;
use spaf\simputils\models\Exp;
use spaf\simputils\PHP;
use function spaf\simputils\basic\pd;

require_once 'vendor/autoload.php';

Logger::$default = new Logger('Profiling with arrays', [
	new CsvFileOutput('/home/ivan/php-simputils-profiling-log'),
	new ContextOutput()
]);

function doProfilingWriting(&$arr, $size): void {
	for ($i = 0; $i < $size; $i++) {
		$arr[] = "text #{$i}";
	}
}

function doProfilingReading(&$arr): void {
	$t = null;
	for ($i = 0; $i < count($arr); $i++) {
		$t = $arr[$i];
	}
}

function profile($name, &$a, $size, ...$params) {
	$dt_start = microtime(true);
	$mem_start = memory_get_usage();
	$name($a, $size, ...$params);
	$mem_end = memory_get_usage();
	$dt_end = microtime(true);
	$dt_res = $dt_end - $dt_start;
	$mem_res = $mem_end - $mem_start;

	return [
		'size' => $size,
		'time' => round($dt_res, 3),
		'memory' => Data::humanReadable($mem_res),
	];
}

$size = 1_000_000;
//
//$a = [];
//$res['Test 1.1, writing normal array'] = profile(
//	'doProfilingWriting',
//	$a,
//	size: $size
//);
//$res['Test 1.2, reading normal array'] = profile(
//	'doProfilingReading',
//	$a,
//	$size
//);
//Logger::info(print_r($res, true));
//unset($a);
//
//$res = [];
//$b = new ArrayObject();
//$res['Test 2.1, writing array-object'] = profile(
//	'doProfilingWriting',
//	$b,
//	size: $size
//);
//$res['Test 2.2, reading array-object'] = profile(
//	'doProfilingReading',
//	$b,
//	size: $size
//);
//Logger::info(print_r($res, true));
//unset($b);
//
//$res = [];
//$c = new Exp();
//$res['Test 3.1, writing exp-object'] = profile(
//	'doProfilingWriting',
//	$c,
//	size: $size
//);
//$res['Test 3.2, reading exp-object'] = profile(
//	'doProfilingReading',
//	$c,
//	size: $size
//);
//Logger::info(print_r($res, true));
//unset($c);

$ts1 = microtime(true);

$phpi = PHP::info();

$prof = round(microtime(true) - $ts1, 3);

pd("READING| time: {$prof} s", "content: {$phpi->size}");

//  ObjectArray
//  READING| time: 0.437 s
//           size: 1000000

//  Simple array:
//	READING| time: 0.286 s
//           size: 1000000
