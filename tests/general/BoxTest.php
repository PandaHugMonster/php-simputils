<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\Version;

/**
 *
 * @covers \spaf\simputils\models\Box
 * @uses \spaf\simputils\traits\PropertiesTrait
 */
class BoxTest extends TestCase {

	/**
	 *
	 * @uses \spaf\simputils\models\Version
	 * @uses \spaf\simputils\versions\DefaultVersionParser
	 *
	 * @runInSeparateProcess
	 * @return void
	 * @throws \Exception
	 */
	public function testBasics() {
		$b1 = new Box();

		$b1[] = 'one';
		$b1[] = 'two';
		$b1[] = 'three';

		$this->assertEquals(3, $b1->size);
		$this->assertInstanceOf(Box::class, $b1->keys);
		$this->assertInstanceOf(Box::class, $b1->values);

		$this->assertEquals(2, $b1->slice(1)->size);
		$this->assertEquals(1, $b1->slice([2])->size);

		$b2 = new Box();
		$b2['key1'] = new Version('1.2.3');
		$b2['key2'] = new Version('2.0.0');
		$b2['key3'] = new Version('3.0.0');
		$b2['key4'] = new Version('4.0.0');

		$this->assertEquals(3, $b2->slice(1)->size);
		$this->assertEquals(2, $b2->slice(['key1', 'key3'])->size);

		$this->assertEquals(2, $b2->slice(-2)->size);
		$this->assertEquals(2, $b2->slice(1, -1)->size);

		$this->expectException(Exception::class);
		$this->assertEquals(2, $b2->slice(10)->size);

	}
}
