<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\exceptions\IPParsingException;
use spaf\simputils\generic\BasicProtocolProcessor;
use spaf\simputils\models\IPv4;
use ValueError;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\ip;
use function spaf\simputils\basic\url;

/**
 *
 * @uses \spaf\simputils\Math
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\PropertiesTrait::__get
 * @uses \spaf\simputils\traits\PropertiesTrait::_simpUtilsPrepareProperty
 * @uses \spaf\simputils\traits\PropertiesTrait::getAllTheLastMethodsAndProperties
 */
class NewFeatures202206Test extends TestCase {

	/**
	 * @covers \spaf\simputils\models\IPv4
	 * @covers \spaf\simputils\basic\ip
	 * @return void
	 */
	function testIpv4() {

		$ip1 = ip('000.001.002.03');
		$this->assertInstanceOf(IPv4::class, $ip1);
		$this->assertEquals('0.1.2.3', "{$ip1}");
		$this->assertEquals(0, $ip1->octet1);
		$this->assertEquals(1, $ip1->octet2);
		$this->assertEquals(2, $ip1->octet3);
		$this->assertEquals(3, $ip1->octet4);
		$this->assertEquals(0, $ip1->mask_cidr);

		$ip2 = ip('5.10.15.20/24');
		$this->assertInstanceOf(IPv4::class, $ip2);
		$this->assertEquals('5.10.15.20/24', "{$ip2}");
		$ip2->output_with_mask = false;
		$this->assertEquals('5.10.15.20', "{$ip2}");

		$this->assertEquals(5, $ip2->octet1);
		$this->assertEquals(10, $ip2->octet2);
		$this->assertEquals(15, $ip2->octet3);
		$this->assertEquals(20, $ip2->octet4);
		$this->assertEquals(24, $ip2->mask_cidr);
		$this->assertEquals('255.255.255.0', $ip2->mask);

		$ip3 = ip('8.8.8.8/0');
		$this->assertNull($ip3->mask_cidr);
	}

	/**
	 * @covers \spaf\simputils\models\IPv4
	 * @covers \spaf\simputils\basic\ip
	 * @dataProvider ipsToCompare
	 * @return void
	 */
	function testIpv4Comparison($left, $right, $result) {
		$left = !$left instanceof IPv4?ip($left):$left;

		if ($result === 0) {
			$this->assertTrue($left->equalsTo($right));
			$this->assertFalse($left->greaterThan($right));
			$this->assertFalse($left->lessThan($right));
		} else if ($result < 0) {
			$this->assertTrue($left->greaterThan($right));
			$this->assertFalse($left->lessThan($right));
			$this->assertFalse($left->equalsTo($right));
		} else {
			$this->assertTrue($left->lessThan($right));
			$this->assertFalse($left->greaterThan($right));
			$this->assertFalse($left->equalsTo($right));
		}
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testIpv4ComparisonException() {
		$this->expectException(ValueError::class);
		$ip1 = ip('1.1.1.1');
		$ip2_not = bx(['1', '1', '1', '1']);
		$ip1->e($ip2_not);
	}

	public function ipsToCompare() {
		return [
			[ip('1.1.1.1'), '01.01.01.1', 0],

			[ip('20.1.1.1'), '1.1.1.1', -1],
			[ip('20.1.1.2'), '20.1.1.1', -1],
			[ip('20.1.10.2'), '20.1.1.2', -1],
			[ip('20.10.10.2'), '20.1.10.2', -1],
			[ip('21.10.10.2'), '20.10.10.2', -1],

			[ip('20.1.1.1'), '20.1.1.2', 1],
			[ip('20.1.1.2'), '20.1.10.2', 1],
			[ip('20.1.10.2'), '20.10.10.2', 1],
			[ip('20.10.10.2'), '21.10.10.2', 1],
			[ip('20.10.10.2'), '255.0.0.0', 1],
			//
			['1.1.1.1', '01.01.01.1', 0],

			['20.1.1.1', '1.1.1.1', -1],
			['20.1.1.2', '20.1.1.1', -1],
			['20.1.10.2', '20.1.1.2', -1],
			['20.10.10.2', '20.1.10.2', -1],
			['21.10.10.2', '20.10.10.2', -1],

			['20.1.1.1', '20.1.1.2', 1],
			['20.1.1.2', '20.1.10.2', 1],
			['20.1.10.2', '20.10.10.2', 1],
			['20.10.10.2', '21.10.10.2', 1],
			['20.10.10.2', '255.0.0.0', 1],
		];
	}

	/**
	 * @dataProvider ipsToParse
	 * @covers \spaf\simputils\models\IPv4
	 *
	 * @param $ip
	 * @param $result
	 *
	 * @return void
	 */
	function testIpv4Parsing($ip, $result) {
		$this->assertEquals($result, IPv4::validate($ip)); // Underlying usage of "parse"
	}

	public function ipsToParse() {
		return [
			['1.2.3.4', true],
			['200.200.200.1', true],
			['000.000.000.000', true],
			['10.0.0.0', true],
			['127.0.1.1', true],
			['127.0.0.1', true],
			['8.8.8.8/0', true], // Mask is empty but specified
			['255.255.255.255', true],
			['1.2.3.4/32', true],
			['1.2.3.4/1', true],
			['1.2.3.4/33', true], // But mask will be 32 anyways

			['256.0.0.0', false],
			['200.300.0.0', false],
			['200.200.1000.0', false],
			['200.200.200.512', false],
			['0000.0.0.1', false],
			['8.8.8.8/', false], // Mask is not specified
		];
	}

	/**
	 * @covers \spaf\simputils\models\IPv4
	 * @runInSeparateProcess
	 *
	 * @return void
	 */
	function testIpv4ParsingException() {
		$this->expectException(IPParsingException::class);
		$res = ip('asdfgh');
	}

	/**
	 * @dataProvider ipRangesToCheck
	 * @covers \spaf\simputils\models\IPv4Range
	 * @covers \spaf\simputils\models\IPv4::range
	 * @uses \spaf\simputils\basic\ip
	 * @uses \spaf\simputils\models\IPv4
	 * @uses \spaf\simputils\traits\ComparablesTrait::lt
	 *
	 * @param $ip1
	 * @param $ip2
	 * @param $result
	 *
	 * @return void
	 */
	function testIpv4Range($ip1, $ip2, $result) {
		$ip1 = ip($ip1);
		$range = $ip1->range($ip2);
		$this->assertEquals($result, "{$range}");
	}

	public function ipRangesToCheck() {
		return [
			['1.1.1.1', '2.2.2.2', '1.1.1.1 - 2.2.2.2'],
			['3.3.3.3', '2.2.2.2', '2.2.2.2 - 3.3.3.3'],
			['8.8.8.8', '8.8.8.8', '8.8.8.8 - 8.8.8.8'],
		];
	}

	/**
	 * @covers \spaf\simputils\models\UrlObject
	 *
	 * @dataProvider urlsToCheck
	 * @param $host
	 * @param $path
	 * @param $params
	 * @param $protocol
	 * @param $data
	 *
	 * @return void
	 */
	function testUrls($result, $host, $path = null, $params = null, $protocol = null, $data = []) {
		$url = url($host, $path, $params, $protocol, ...$data);

		if (!empty($result[0])) {
			$this->assertEquals($result[0], $url->protocol);
			$this->assertInstanceOf(BasicProtocolProcessor::class, $url->processor);
		}
		if (!empty($result[1])) {
			$this->assertEquals($result[1], $url->host);
		}
		if (!empty($result[2])) {
			$this->assertEquals($result[2], "{$url->path}");
		}
		if (!empty($result[3])) {
			$this->assertEquals($result[3], "{$url->params}");
		}
		if (!empty($result[4])) {
			$this->assertEquals($result[4], $url->data);
		}
	}

	public function urlsToCheck() {
		return [
			[
				['https', 'crud.science'],
				'https://crud.science',
			],
			[
				[null, 'google.com', '["special-path-that-does-not-exist"]'],
				'//google.com/special-path-that-does-not-exist',
			],
			[
				[null, 'google.com', '["special-path-that-does-not-exist","additional","path","part"]'],
				'//google.com/special-path-that-does-not-exist',
				['additional', 'path', 'part']
			],
		];
	}
}
