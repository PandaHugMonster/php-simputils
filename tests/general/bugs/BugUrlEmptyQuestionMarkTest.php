<?php

namespace general\bugs;

use PHPUnit\Framework\TestCase;
use function spaf\simputils\basic\url;

/**
 * @covers \spaf\simputils\models\UrlObject
 * @uses \spaf\simputils\Boolean
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\basic\bx
 * @uses \spaf\simputils\basic\url
 * @uses \spaf\simputils\components\normalizers\BooleanNormalizer
 * @uses \spaf\simputils\components\normalizers\StringNormalizer
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\models\urls\processors\HttpProtocolProcessor
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\PropertiesTrait
 */
class BugUrlEmptyQuestionMarkTest extends TestCase {

	function testOne() {
		$url = url('localhost', '//test/test?');

		$this->assertEmpty((array) $url->params);
		$this->assertEquals('/test/test', $url->relative);
	}

}
