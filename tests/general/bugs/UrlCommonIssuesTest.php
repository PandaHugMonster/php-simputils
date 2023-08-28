<?php

namespace general\bugs;

use PHPUnit\Framework\TestCase;
use function spaf\simputils\basic\url;

/**
 * @covers \spaf\simputils\models\UrlObject
 * @uses   \spaf\simputils\Boolean
 * @uses   \spaf\simputils\PHP
 * @uses   \spaf\simputils\Str
 * @uses   \spaf\simputils\attributes\Property
 * @uses   \spaf\simputils\basic\bx
 * @uses   \spaf\simputils\basic\url
 * @uses   \spaf\simputils\components\normalizers\BooleanNormalizer
 * @uses   \spaf\simputils\components\normalizers\StringNormalizer
 * @uses   \spaf\simputils\models\Box
 * @uses   \spaf\simputils\models\urls\processors\HttpSchemeProcessor
 * @uses   \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses   \spaf\simputils\traits\PropertiesTrait
 */
class UrlCommonIssuesTest extends TestCase {

	/**
	 * Empty Question Mark parsing issue testing
	 *
	 * @return void
	 */
	function testOne() {
		$url = url('localhost', '//test/test?');

		$this->assertEmpty((array) $url->params);
		$this->assertEquals('/test/test', $url->relative);
	}

	/**
	 * Empty value parameter or 0 is being skipped/ignored in parameters
	 *
	 * @uses \spaf\simputils\basic\ic
	 * @return void
	 */
	function testTwo() {
		$link = url(
			path: '/test/test2/test3?g1=1&g2=2&G3=0',
			params: ['test10' => 10, 'test20' => '', 'test30' => null, 'test40' => '0']
		);

		$this->assertArrayHasKey('g1', $link->params);
		$this->assertArrayHasKey('g2', $link->params);
		$this->assertArrayHasKey('G3', $link->params);
		$this->assertArrayHasKey('test10', $link->params);
		$this->assertArrayHasKey('test20', $link->params);
		$this->assertArrayHasKey('test30', $link->params);
		$this->assertArrayHasKey('test40', $link->params);

		$this->assertEquals(1, $link->params['g1']);
		$this->assertEquals(2, $link->params['g2']);
		$this->assertEquals(0, $link->params['G3']);
		$this->assertEquals(10, $link->params['test10']);
		$this->assertEmpty($link->params['test20']);
		$this->assertEmpty($link->params['test30']);
		$this->assertEquals('0', $link->params['test40']);

	}

}
