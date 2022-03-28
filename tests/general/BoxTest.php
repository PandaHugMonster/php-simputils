<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use function spaf\simputils\basic\bx;

/**
 * @covers \spaf\simputils\models\Box
 * @covers \spaf\simputils\PHP::box
 * @covers \spaf\simputils\basic\bx
 *
 * @uses \spaf\simputils\PHP::isClass
 * @uses \spaf\simputils\PHP::redef
 * @uses \spaf\simputils\PHP::type
 * @uses \spaf\simputils\Str::is
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____prepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 */
class BoxTest extends TestCase {

	/**
	 *
	 * @return void
	 * @throws \Exception
	 *
	 * @runInSeparateProcess
	 */
	public function testBasics() {
		$box_class = PHP::redef(Box::class);

		$b1 = new $box_class();
		$version_class = PHP::redef(Version::class);

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
	 * @return void
	 * @throws \Exception
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
