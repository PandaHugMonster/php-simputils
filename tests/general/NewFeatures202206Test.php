<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\components\normalizers\IPNormalizer;
use spaf\simputils\components\normalizers\UrlNormalizer;
use spaf\simputils\DT;
use spaf\simputils\exceptions\IPParsingException;
use spaf\simputils\models\IPv4;
use spaf\simputils\models\UrlObject;
use ValueError;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\ip;
use function spaf\simputils\basic\ts;
use function spaf\simputils\basic\url;

/**
 *
 * @uses \spaf\simputils\Math
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\PropertiesTrait::__get
 * @uses \spaf\simputils\traits\PropertiesTrait::__set
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

//	function testUrls($result, $host, $path = null, $params = null, $protocol = null, $data = []) {
//		$url = url($host, $path, $params, $protocol, ...$data);
//
//		if (!empty($result[0])) {
//			$this->assertEquals($result[0], $url->protocol);
//			$this->assertInstanceOf(BasicProtocolProcessor::class, $url->processor);
//		}
//		if (!empty($result[1])) {
//			$this->assertEquals($result[1], $url->host);
//		}
//		if (!empty($result[2])) {
//			$this->assertEquals($result[2], "{$url->path}");
//		}
//		if (!empty($result[3])) {
//			$this->assertEquals($result[3], "{$url->params}");
//		}
//		if (!empty($result[4])) {
//			$this->assertEquals($result[4], $url->data);
//		}
//	}

	/**
	 * @covers \spaf\simputils\models\UrlObject
	 * @covers \spaf\simputils\basic\url
	 * @covers \spaf\simputils\models\urls\processors\HttpProtocolProcessor
	 *
	 * @uses \spaf\simputils\Boolean::from
	 * @uses \spaf\simputils\basic\bx
	 * @uses \spaf\simputils\components\normalizers\BooleanNormalizer
	 * @uses \spaf\simputils\components\normalizers\StringNormalizer
	 * @uses \spaf\simputils\generic\BasicProtocolProcessor
	 * @uses \spaf\simputils\models\Box
	 * @uses \spaf\simputils\traits\PropertiesTrait::__set
	 * @uses \spaf\simputils\traits\PropertiesTrait::_simpUtilsGetValidator
	 * @uses \spaf\simputils\basic\ip
	 * @uses \spaf\simputils\models\IPv4
	 * @uses \spaf\simputils\traits\PropertiesTrait::__isset
	 *
	 * @return void
	 */
	function testUrlsAdditional() {
		$url = url('gitlab.com?cyr=%D0%9F%D1%80%D0%B8%D0%B2%D0%B5%D1%82+%D0%9C%D0%B8%D1%80%21' .
			'&some-more=arg1&another-arg=2&test=test#goooo');

		$url->path = 'what/is/the/path';
//		$url->addPath('what/is/the/path');
//		$this->assertEquals(bx(['what', 'is', 'the', 'path']), $url->path);
//
//		$p = bx(['some' => 'data', 'here' => 'it', 'is' => '!']);
//		$url->addData($p);
//		$this->assertEquals($p, $url->data);

		$this->assertEquals('https://gitlab.com/what/is/the/path' .
			'?cyr=%D0%9F%D1%80%D0%B8%D0%B2%D0%B5%D1%82+%D0%9C%D0%B8%D1%80%21' .
			'&some-more=arg1&another-arg=2&test=test#goooo', $url->for_system);
		$this->assertEquals('https://gitlab.com/what/is/the/path' .
			'?cyr=%D0%9F%D1%80%D0%B8%D0%B2%D0%B5%D1%82+%D0%9C%D0%B8%D1%80%21' .
			'&some-more=arg1&another-arg=2&test=test#goooo', $url->for_user);
		$this->assertEquals('/what/is/the/path?cyr=%D0%9F%D1%80%D0%B8%D0%B2%D0%B5%D1%82+' .
			'%D0%9C%D0%B8%D1%80%21&some-more=arg1&another-arg=2&test=test#goooo', $url->relative);

		$this->assertEquals('Привет Мир!', $url->params['cyr']);

		$url = url(
			ip('1.2.3.4'),
			'/some/some/some',
			['p1' => 'v1', 'p2' => 'v2', '#' => 'goo', 'cyr' => 'Привет Мир!']
		);

		$this->assertEquals(
			'https://1.2.3.4/some/some/some' .
			'?p1=v1&p2=v2&cyr=%D0%9F%D1%80%D0%B8%D0%B2%D0%B5%D1%82+%D0%9C%D0%B8%D1%80%21#goo',
			$url->for_system
		);

	}

//	function testUrlsException1() {
//		$this->expectException(ProtocolProcessorIsUndefined::class);
//		url(protocol: 'topotemkin');
//	}

	public function urlsToCheck() {
		return [
			[
				['https', 'crud.science'],
				'https://crud.science',
			],
			[
				[null, 'google.com', 'special-path-that-does-not-exist'],
				'//google.com/special-path-that-does-not-exist',
			],
			[
				[null, 'google.com', 'special-path-that-does-not-exist/additional/path/part'],
				'//google.com/special-path-that-does-not-exist',
				['additional', 'path', 'part']
			],
			[
				[null, 'google.com', 'special-path-that-does-not-exist/additional/path/part'],
				'//google.com/special-path-that-does-not-exist',
				['additional', 'path', 'part', 'another-additional-param-arg' => 'wut?!'], ['additional' => 'param-arg']
			],
			[
				[null, null, 'just/path/stuff'],
				['just', 'path', 'stuff', 'and_this_is_argument' => 'stuff'],
			],
		];
	}

	/**
	 * @covers \spaf\simputils\models\DateTime
	 * @covers \spaf\simputils\DT
	 * @covers \spaf\simputils\basic\ts
	 *
	 * @dataProvider dateTimeAdditionalFieldsToCheck
	 * @param $val
	 * @param $result
	 *
	 * @return void
	 */
	function testDateTimeAdditionalFields($val, $result) {
		$dt = ts($val, 'UTC');

		$this->assertEquals($result[0], $dt->dow_iso);
		$this->assertEquals($result[1], $dt->dow);
		$this->assertEquals($result[2], $dt->is_weekday);
		$this->assertEquals($result[3], $dt->is_weekend);

		$k = 'Europe/Vienna';
		$dt->setTimezone($k);
		$this->assertTrue("$dt->tz" === "{$k}");

		$k = '';
		$dt->setTimezone($k);
		$this->assertTrue("$dt->tz" === "UTC");
	}

	public function dateTimeAdditionalFieldsToCheck() {
		return [
			['1990-02-22', [4, 4, true, false]],

			['1990-01-01', [1, 1, true, false]],
			['1990-01-02', [2, 2, true, false]],
			['1990-01-03', [3, 3, true, false]],
			['1990-01-04', [4, 4, true, false]],
			['1990-01-05', [5, 5, true, false]],
			['1990-01-06', [6, 6, false, true]],
			['1990-01-07', [7, 0, false, true]],

			['1990-01-08', [1, 1, true, false]],
			['1990-01-09', [2, 2, true, false]],
			['1990-01-10', [3, 3, true, false]],
			['1990-01-11', [4, 4, true, false]],
			['1990-01-12', [5, 5, true, false]],
			['1990-01-13', [6, 6, false, true]],
			['1990-01-14', [7, 0, false, true]],

		];
	}

	/**
	 * @covers \spaf\simputils\models\DateTime
	 * @covers \spaf\simputils\models\DateInterval
	 * @covers \spaf\simputils\models\DatePeriod
	 *
	 * @uses \spaf\simputils\DT
	 * @uses \spaf\simputils\basic\ts
	 * @uses \spaf\simputils\models\DatePeriod
	 * @uses \spaf\simputils\traits\MetaMagic::___setup
	 * @uses \spaf\simputils\traits\MetaMagic::_metaMagic
	 * @uses \spaf\simputils\traits\MetaMagic::expandFrom
	 * @uses \spaf\simputils\traits\SimpleObjectTrait
	 *
	 * @dataProvider datetimePeriodToCheck
	 *
	 * @return void
	 */
	function testDateTimePeriodFunctionality($dt1, $dt2, $is_direct, $result, $interval_result) {
		/** @var \spaf\simputils\models\DatePeriod $period */
		$period = $dt1->period($dt2, is_direct_only: $is_direct);
		$this->assertEquals($result, "{$period}");

		$this->assertEquals($interval_result, "{$period->extended_interval}");
	}

	public function datetimePeriodToCheck() {
		return [
			[
				ts('1990-01-01', 'UTC'), ts('1991-01-01', 'UTC'), false,
				'1990-01-01 00:00:00 - 1991-01-01 00:00:00', '+ 1 year'
			],
			[
				ts('2000-01-01', 'UTC'), ts('1991-01-01', 'UTC'), false,
				'2000-01-01 00:00:00 - 1991-01-01 00:00:00', '- 9 years'
			],
			[
				ts('2000-01-01', 'UTC'), ts('1991-01-01', 'UTC'), true,
				'1991-01-01 00:00:00 - 2000-01-01 00:00:00', '+ 9 years'
			],
			[
				ts('2500-01-01', 'UTC'), ts('1991-01-01', 'UTC'), true,
				'1991-01-01 00:00:00 - 2500-01-01 00:00:00', '+ 509 years'
			],

			[
				ts('1990-01-01', 'UTC'), '+20 days', false,
				'1990-01-01 00:00:00 - 1990-01-21 00:00:00', '+ 20 days'
			],
			[
				ts('2000-01-01', 'UTC'), '-22 days', false,
				'2000-01-01 00:00:00 - 1999-12-10 00:00:00', '- 22 days'
			],
			[
				ts('2000-01-01', 'UTC'), '+21 days', true,
				'2000-01-01 00:00:00 - 2000-01-22 00:00:00', '+ 21 days'
			],
			[
				ts('2500-01-01', 'UTC'), '-18 days', true,
				'2499-12-14 00:00:00 - 2500-01-01 00:00:00', '+ 18 days'
			],
		];
	}

	/**
	 * @covers \spaf\simputils\components\normalizers\IPNormalizer
	 * @covers \spaf\simputils\components\normalizers\UrlNormalizer
	 *
	 * @uses \spaf\simputils\basic\bx
	 * @uses \spaf\simputils\Boolean
	 * @uses \spaf\simputils\components\normalizers\BooleanNormalizer
	 * @uses \spaf\simputils\components\normalizers\StringNormalizer
	 * @uses \spaf\simputils\generic\BasicProtocolProcessor
	 * @uses \spaf\simputils\models\Box
	 * @uses \spaf\simputils\models\IPv4
	 * @uses \spaf\simputils\models\UrlObject
	 * @uses \spaf\simputils\models\urls\processors\HttpProtocolProcessor
	 * @uses \spaf\simputils\traits\PropertiesTrait::__set
	 * @uses \spaf\simputils\traits\PropertiesTrait::_simpUtilsGetValidator
	 *
	 * @return void
	 */
	function testNormalizers() {
		$ip = IPNormalizer::process('1.1.1.1');
		$this->assertInstanceOf(IPv4::class, $ip);

		$url = UrlNormalizer::process('http://dw.com/?arg1=t1&arg2=t2#no-page');
		$this->assertInstanceOf(UrlObject::class, $url);
	}

	/**
	 *
	 * @covers \spaf\simputils\DT
	 * @uses \spaf\simputils\basic\bx
	 *
	 * @return void
	 */
	function testDT() {
		$this->assertEquals(bx([
			'Sunday', 'Monday', 'Tuesday',
			'Wednesday', 'Thursday', 'Friday', 'Saturday'
		]), DT::getListOfDaysOfWeek(false));

		$this->assertEquals(bx(
			[
				1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
				5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
			]
		), DT::getListOfDaysOfWeek(true));

		$this->assertEquals(bx(
			[
				1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May',
				6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October',
				11 => 'November', 12 => 'December',
			]
		), DT::getListOfMonths());
	}
}
