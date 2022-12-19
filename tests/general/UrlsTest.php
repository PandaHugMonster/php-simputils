<?php

namespace general;

use PHPUnit\Framework\TestCase;
use function is_null;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\url;

/**
 * @covers \spaf\simputils\models\UrlObject
 * @covers \spaf\simputils\models\urls\processors\HttpProtocolProcessor
 * @covers \spaf\simputils\basic\url
 * @covers \spaf\simputils\generic\BasicProtocolProcessor
 *
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\traits\MetaMagic::_jsonFlags
 * @uses \spaf\simputils\traits\MetaMagic::toJson
 * @uses \spaf\simputils\Boolean
 * @uses \spaf\simputils\components\normalizers\BooleanNormalizer
 * @uses \spaf\simputils\components\normalizers\StringNormalizer
 * @uses \spaf\simputils\traits\PropertiesTrait::__set
 * @uses \spaf\simputils\traits\PropertiesTrait::_simpUtilsGetValidator
 */
class UrlsTest extends TestCase {

	function dataProviderBasicsStrOnly() {
		return [
			[
				'/path1/path2/?dat1=res1&dat2=res2#gg',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 80,
					'path' => ['path1', 'path2'],
					'params' => ['dat1' => 'res1', 'dat2' => 'res2'],
					'data' => ['sharpy' => 'gg'],
				]
			],
			[
				'path1/path2/?dat1=res1&dat2=res2#gg',
				[
					'protocol' => 'https',
					'host' => 'path1',
					'port' => 80,
					'path' => ['path2'],
					'params' => ['dat1' => 'res1', 'dat2' => 'res2'],
					'data' => ['sharpy' => 'gg'],
				]
			],
			[
				'localhost/p4/p5/p6?d1=v1&d2=v2&d3=v3',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 80,
					'path' => ['p4', 'p5', 'p6'],
					'params' => ['d1' => 'v1', 'd2' => 'v2', 'd3' => 'v3'],
					// 'data' => ['sharpy' => null],
				]
			],
			[
				'http://localhost:9090/p1/p2/p3?',
				[
					'protocol' => 'http',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p1', 'p2', 'p3'],
					'params' => [],
					// 'data' => ['sharpy' => null],
				]
			],
			[
				'https://localhost:9090/p4/p5/p6?d1=v1&d2=v2&d3=v3',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p4', 'p5', 'p6'],
					'params' => [
						'd1' => 'v1',
						'd2' => 'v2',
						'd3' => 'v3',
					],
					// 'data' => ['sharpy' => null],
				]
			],
			[
				'//localhost:9090/p4/p5/p6?d1=v1&d2=v2&d3=v3',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p4', 'p5', 'p6'],
					'params' => [
						'd1' => 'v1',
						'd2' => 'v2',
						'd3' => 'v3',
					],
					// 'data' => ['sharpy' => null],
				]
			],
			[
				'localhost:9090/p7/p8/p9?d4=v4&d5=v5&d6=v6',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p7', 'p8', 'p9'],
					'params' => [
						'd4' => 'v4',
						'd5' => 'v5',
						'd6' => 'v6',
					],
					// 'data' => ['sharpy' => null],
				]
			],
			[
				':8080/path1/path2/?dat1=res1&dat2=res2#gg',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 8080,
					'path' => ['path1', 'path2'],
					'params' => [
						'dat1' => 'res1',
						'dat2' => 'res2',
					],
					'data' => ['sharpy' => 'gg'],
				]
			],
			[
				'?k1=Y1&k2=Y2#ggogg',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 80,
					'path' => [],
					'params' => [
						'k1' => 'Y1',
						'k2' => 'Y2',
					],
					'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'http://dd:gg@localhost:9090/p1/p2/p3?',
				[
					'protocol' => 'http',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p1', 'p2', 'p3'],
					'user' => 'dd',
					'password' => 'gg',
					'params' => [],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'https://dd:@localhost:9090/p4/p5/p6?d1=v1&d2=v2&d3=v3',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p4', 'p5', 'p6'],
					'user' => 'dd',
					'password' => null,
					'params' => [
						'd1' => 'v1',
						'd2' => 'v2',
						'd3' => 'v3',
					],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'//dd@localhost:9090/p4/p5/p6?d1=v1&d2=v2&d3=v3',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p4', 'p5', 'p6'],
					'user' => 'dd',
					// 'password' => null,
					'params' => [
						'd1' => 'v1',
						'd2' => 'v2',
						'd3' => 'v3',
					],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'DD:EE@localhost:9090/p7/p8/p9?d4=v4&d5=v5&d6=v6',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 9090,
					'path' => ['p7', 'p8', 'p9'],
					'user' => 'DD',
					'password' => 'EE',
					'params' => [
						'd4' => 'v4',
						'd5' => 'v5',
						'd6' => 'v6',
					],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'http://dd:gg@localhost/p1/p2/p3?',
				[
					'protocol' => 'http',
					'host' => 'localhost',
					'port' => 80,
					'path' => ['p1', 'p2', 'p3'],
					'user' => 'dd',
					'password' => 'gg',
					'params' => [],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'https://dd:@localhost/p4/p5/p6?d1=v1&d2=v2&d3=v3',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 80,
					'path' => ['p4', 'p5', 'p6'],
					'user' => 'dd',
					'password' => null,
					'params' => [
						'd1' => 'v1',
						'd2' => 'v2',
						'd3' => 'v3',
					],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'//dd@localhost/p4/p5/p6?d1=v1&d2=v2&d3=v3',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 80,
					'path' => ['p4', 'p5', 'p6'],
					'user' => 'dd',
					'password' => null,
					'params' => [
						'd1' => 'v1',
						'd2' => 'v2',
						'd3' => 'v3',
					],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
			[
				'DD:EE@localhost/p7/p8/p9?d4=v4&d5=v5&d6=v6',
				[
					'protocol' => 'https',
					'host' => 'localhost',
					'port' => 80,
					'path' => ['p7', 'p8', 'p9'],
					'user' => 'DD',
					'password' => 'EE',
					'params' => [
						'd4' => 'v4',
						'd5' => 'v5',
						'd6' => 'v6',
					],
					// 'data' => ['sharpy' => 'ggogg'],
				]
			],
		];
	}

	private function _checkSimple($name, $res, $expected) {
		$tst = $res?->$name ?: null;
		if (bx($expected)->containsKey($name)) {
			$exp = $expected[$name] ?? null;
			if (is_null($exp)) {
				$this->assertNull($tst);
			} else {
				$this->assertEquals($exp, $tst);
			}
		}
	}

	private function _checkComplex($name, $res, $expected) {
		$tst = $res?->$name ?: null;
		if (bx($expected)->containsKey($name)) {
			$exp = bx($expected[$name]) ?? null;
			if (is_null($exp)) {
				$this->assertNull($tst);
			} else {
				$this->assertEquals($exp, $tst);
			}
		}
	}

	/**
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\attributes\Property::expectedName
	 * @uses \spaf\simputils\attributes\Property::methodAccessType
	 * @uses \spaf\simputils\attributes\Property::subProcess
	 * @uses \spaf\simputils\basic\bx
	 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
	 * @uses \spaf\simputils\traits\PropertiesTrait::__get
	 * @uses \spaf\simputils\traits\PropertiesTrait::_simpUtilsPrepareProperty
	 * @uses \spaf\simputils\traits\PropertiesTrait::getAllTheLastMethodsAndProperties
	 * @dataProvider dataProviderBasicsStrOnly
	 * @covers \spaf\simputils\models\UrlObject
	 */
	function testBasicsStrOnly($str, $expected) {
		$this->_checkAll(url($str), $expected);
	}

	private function _checkAll($res, $expected) {
		$this->_checkSimple('protocol', $res, $expected);
		$this->_checkSimple('user', $res, $expected);
		$this->_checkSimple('password', $res, $expected);
		$this->_checkSimple('host', $res, $expected);
		$this->_checkSimple('port', $res, $expected);
		$this->_checkComplex('path', $res, $expected);
		$this->_checkComplex('params', $res, $expected);
		$this->_checkComplex('data', $res, $expected);
	}

	/**
	 * @covers \spaf\simputils\models\UrlObject
	 *
	 * @uses \spaf\simputils\PHP
	 * @uses \spaf\simputils\Boolean
	 * @uses \spaf\simputils\models\Box
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\attributes\Property::expectedName
	 * @uses \spaf\simputils\attributes\Property::methodAccessType
	 * @uses \spaf\simputils\attributes\Property::subProcess
	 * @uses \spaf\simputils\basic\bx
	 * @uses \spaf\simputils\components\normalizers\BoxNormalizer
	 * @uses \spaf\simputils\components\normalizers\IntegerNormalizer
	 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
	 * @uses \spaf\simputils\traits\PropertiesTrait::__get
	 * @uses \spaf\simputils\traits\PropertiesTrait::_simpUtilsPrepareProperty
	 * @uses \spaf\simputils\traits\PropertiesTrait::getAllTheLastMethodsAndProperties
	 */
	function testBasicsExtension() {
		$str = 'localhost:90/booo/fooo?godzila=tamdam#jjj';
		$url = url($str);

		$this->_checkAll($url, [
			'host' => 'localhost',
			'port' => 90,
			'path' => ['booo', 'fooo'],
			'params' => [
				'godzila' => 'tamdam',
			],
			'data' => ['sharpy' => 'jjj']
		]);

		// NOTE Checking path-string (extended with params and stuff)
		$this->_checkAll(url($str, 'test1/test2/test3?param1=val1#ddd'), [
			'host' => 'localhost',
			'path' => ['booo', 'fooo', 'test1', 'test2', 'test3'],
			'params' => [
				'godzila' => 'tamdam',
				'param1' => 'val1'
			],
			'data' => ['sharpy' => 'ddd'],
		]);

		// NOTE Checking path-string (extended with params and stuff)
		$this->_checkAll(url($str, '?param2=val2&param3=bbaa'), [
			'host' => 'localhost',
			'path' => ['booo', 'fooo'],
			'params' => [
				'godzila' => 'tamdam',
				'param2' => 'val2',
				'param3' => 'bbaa',
			],
			'data' => ['sharpy' => 'jjj'],
		]);

		// NOTE Checking path-string
		$this->_checkAll(url($str, 'test1/test2/test3'), [
			'host' => 'localhost',
			'path' => ['booo', 'fooo', 'test1', 'test2', 'test3'],
		]);

		// NOTE Checking path-array
		$this->_checkAll(url($str, ['test1', 'test2', 'test3']), [
			'host' => 'localhost',
			'path' => ['booo', 'fooo', 'test1', 'test2', 'test3'],
		]);

		// NOTE Checking path-box
		$this->_checkAll(url($str, bx(['test1', 'test2', 'test3'])), [
			'host' => 'localhost',
			'path' => ['booo', 'fooo', 'test1', 'test2', 'test3'],
		]);

		////////////////////

		// NOTE Checking params
		$this->_checkAll(url($str, 'test1/test2/test3', ['p1p1' => 'v2v2', 'p2p2' => 'v2v2']), [
			'host' => 'localhost',
			'path' => ['booo', 'fooo', 'test1', 'test2', 'test3'],
			'params' => [
				'godzila' => 'tamdam',
				'p1p1' => 'v2v2',
				'p2p2' => 'v2v2',
			],
			'data' => ['sharpy' => 'jjj'],
		]);
		$this->_checkAll(url($str, 'test1/test2/test3?ddd=qqq',
			['p1p1' => 'v2v2', 'p2p2' => 'v2v2', '#' => 'MMM'], 'http', port: 89), [

			'host' => 'localhost',
			'protocol' => 'http',
			'path' => ['booo', 'fooo', 'test1', 'test2', 'test3'],
			'port' => 89,
			'params' => [
				'godzila' => 'tamdam',
				'p1p1' => 'v2v2',
				'p2p2' => 'v2v2',
				'ddd' => 'qqq',
			],
			'data' => ['sharpy' => 'MMM'],
		]);
		$this->_checkAll(url($str, ['pa1', 'pa2', 'test', 'pa3'],
			['p1p1' => 'v2v2', 'p2p2' => 'v2v2', '#' => 'something-special_or_not'],
			'http', port: 9999), [

			'host' => 'localhost',
			'protocol' => 'http',
			'path' => ['booo', 'fooo', 'pa1', 'pa2', 'test', 'pa3'],
			'port' => 9999,
			'params' => [
				'godzila' => 'tamdam',
				'p1p1' => 'v2v2',
				'p2p2' => 'v2v2',
			],
			'data' => ['sharpy' => 'something-special_or_not'],
		]);

		$url = url($str, ['pa1', 'pa2', 'test', 'pa3'],
			['p1p1' => 'v2v2', 'p2p2' => 'v2v2', '#' => 'something-special_or_not'],
			'http', port: 9999);
		$url->host = 'doggy.nougat.dot-com';
		$this->_checkAll($url, [
			'host' => 'doggy.nougat.dot-com',
			'protocol' => 'http',
			'path' => ['booo', 'fooo', 'pa1', 'pa2', 'test', 'pa3'],
			'port' => 9999,
			'params' => [
				'godzila' => 'tamdam',
				'p1p1' => 'v2v2',
				'p2p2' => 'v2v2',
			],
			'data' => ['sharpy' => 'something-special_or_not'],
		]);

		$url->host = 'ttt-tttt-ttt.tt_to';
		$url->protocol = 'https';
		$url->path = bx(['ddd']);
		$url->port = 774;
		$url->params = bx(['k1' => 'v1']);
		$url->data['sharpy'] = 'cow';

		$this->_checkAll($url, [
			'host' => 'ttt-tttt-ttt.tt_to',
			'protocol' => 'https',
			'path' => ['ddd'],
			'port' => 774,
			'params' => [
				'k1' => 'v1',
			],
			'data' => ['sharpy' => 'cow'],
		]);

		// NOTE Using arrays instead of Box
		$url->host = 'aannootthheerr';
		$url->protocol = 'http';
		$url->path = ['ddd'];
		$url->port = 774;
		$url->params = ['k1' => 'v1'];
		$url->sharpy = 'cow';

		$this->_checkAll($url, [
			'host' => 'aannootthheerr',
			'protocol' => 'http',
			'path' => ['ddd'],
			'port' => 774,
			'params' => [
				'k1' => 'v1',
			],
			'data' => ['sharpy' => 'cow'],
		]);

		// NOTE Using strings for path setting
		$url->path = 'another/path/or/what/ever';

		$url->host = 'one-more-thing';
		$url->protocol = 'https';
		$url->port = 800;
		$url->sharpy = 'goat';

		$this->_checkAll($url, [
			'host' => 'one-more-thing',
			'protocol' => 'https',
			'path' => ['another', 'path', 'or', 'what', 'ever'],
			'port' => 800,
			'params' => [
				'k1' => 'v1',
			],
			'data' => ['sharpy' => 'goat'],
		]);

//		pd($url);
	}

}
