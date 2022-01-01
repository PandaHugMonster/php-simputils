<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\Version;
use spaf\simputils\special\CodeBlocksCacheIndex;

/**
 *
 * @covers \spaf\simputils\models\Box
 * @uses \spaf\simputils\traits\PropertiesTrait
 */
class BoxTest extends TestCase {

	/**
	 *
	 * @return void
	 * @throws \Exception
	 *@uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\components\versions\parsers\DefaultVersionParser
	 *
	 * @runInSeparateProcess
	 */
	public function testBasics() {
		$box_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_BOX,
			Box::class
		);
		$b1 = new $box_class();
		$version_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);

		$b1[] = 'one';
		$b1[] = 'two';
		$b1[] = 'three';

		$this->assertEquals(3, $b1->size);
		$this->assertInstanceOf($box_class::class, $b1->keys);
		$this->assertInstanceOf($box_class::class, $b1->values);

		$this->assertEquals(2, $b1->slice(1)->size);
		$this->assertEquals(1, $b1->slice([2])->size);

		$b2 = new $box_class();
		$b2['key1'] = new $version_class('1.2.3');
		$b2['key2'] = new $version_class('2.0.0');
		$b2['key3'] = new $version_class('3.0.0');
		$b2['key4'] = new $version_class('4.0.0');

		$this->assertEquals(3, $b2->slice(1)->size);
		$this->assertEquals(2, $b2->slice(['key1', 'key3'])->size);

		$this->assertEquals(2, $b2->slice(-2)->size);
		$this->assertEquals(2, $b2->slice(1, -1)->size);

		$this->expectException(Exception::class);
		$this->assertEquals(2, $b2->slice(10)->size);

	}
}
