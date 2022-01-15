<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\InitConfig;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use spaf\simputils\special\CodeBlocksCacheIndex;
use function spaf\simputils\basic\bx;

/**
 *
 * @covers \spaf\simputils\models\Box
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\PHP::box
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\basic\bx
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
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
		$box_class = PHP::redef(Box::class);

		$b1 = new $box_class();
		$version_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_VERSION,
			Version::class
		);

		$b1[] = 'one';
		$b1[] = 'two';
		$b1[] = 'three';

		$this->assertEquals(3, $b1->size);
		$this->assertInstanceOf($box_class, $b1->keys);
		$this->assertInstanceOf($box_class, $b1->values);

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

	/**
	 * @covers \spaf\simputils\models\Box::getFlipped
	 * @uses \spaf\simputils\PHP::type
	 * @return void
	 */
	function testAdditionalStuff() {
		$data = bx([
			'key1' => 'val1',
			'key2' => 'val2',
			'key3' => 'val3',
		]);
		$flipped = $data->flipped;

		$this->assertArrayHasKey('val2', $flipped);
		$this->assertArrayNotHasKey('key1', $flipped);
		$this->assertNotEquals($data, $flipped);

		$this->assertNotEquals($data->obj_id, $flipped->obj_id);
		$this->assertNotEquals($data->obj_id, $data->clone()->obj_id);
		$this->assertEquals($data->obj_type, $flipped->obj_type);

		// Checks not id of objects but content! ^_^
		$this->assertEquals($data, clone $data);
		$this->assertEquals(clone $data, $data->clone());

		$this->assertEquals(3, $data->size);
		$this->assertEquals(2, $data->shift()->size);
		$this->assertEquals(bx(['key1' => 'val1']), $data->stash);
		$this->assertEquals(bx(['key3' => 'val3']), $data->shift(from_start: false)->stash);

		$this->assertEquals(1, $data->size);
		$this->assertEquals(bx(['key2' => 'val2']), $data);

	}
}
