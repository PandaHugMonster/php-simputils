<?php


use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\Set;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;

/**
 * @covers \spaf\simputils\models\Box
 * @covers \spaf\simputils\models\StackFifo
 * @covers \spaf\simputils\models\StackLifo
 *
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str::is
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsPrepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 * @uses \spaf\simputils\Math
 * @uses \spaf\simputils\traits\SimpleObjectTrait::_simpUtilsGetValidator
 * @uses \spaf\simputils\traits\MetaMagic::_jsonFlags
 * @uses \spaf\simputils\traits\MetaMagic::toJson
 */
class BoxTest extends TestCase {

	/**
	 *
	 * @return void
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

		$this->expectException(ValueError::class);
		$this->assertEquals(2, $b2->slice(10)->size);
	}

	/**
	 * @uses \spaf\simputils\PHP::box
	 * @uses \spaf\simputils\Str
	 * @uses \spaf\simputils\models\Set
	 *
	 * @return void
	 */
	function testAdditionalStuff() {
		$data = PHP::box([
			'key1' => 'val1',
			'key2' => 'val2',
			'key3' => 'val3',
		]);
		$flipped = $data->flipped();

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
		$this->assertEquals(PHP::box(['key1' => 'val1']), $data->stash);
		$this->assertEquals(PHP::box(['key3' => 'val3']), $data->shift(from_start: false)->stash);

		$this->assertEquals(1, $data->size);
		$this->assertEquals(PHP::box(['key2' => 'val2']), $data);

		$this->assertEquals('key2', $data->getKeyByValue('val2'));
		$this->assertEquals('val2', $data->get('key2'));
		$this->assertEquals('val2', $data->get('KEY2', case_sensitive: false));
		$this->assertEquals(
			'default text',
			$data->get('test', 'default text', case_sensitive: false)
		);

		$bx = PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']);

		// FIX  Improve these tests
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort()
		);
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort(true, false, false, false)
		);
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort(true, true, false, false)
		);
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort(true, true, true, false)
		);
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort(true, true, true, true)
		);
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort(true, true, true, true, function () {
				return 0;
			})
		);
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort(true, false, true, true, function () {
				return 0;
			})
		);
		$this->assertEquals(
			PHP::box(['b_abc_123', 'a_abc_123', 'c_abc_123', 'd_abc_123', 'y_abc_123']),
			$bx->sort(false, false, true, true)
		);

		$bx = PHP::box(['test1', 'test2'], ['test3', 'test4'])->join();
		$this->assertEquals('test1, test2, test3, test4', $bx);

		$bx0 = $bx = PHP::box(
			['t1' => 'test1', 't2' => 'test2', 't3' => 'lol'],
			['t3' => 'test3', 't4' => 'test4']
		);

		$bx = $bx->join(' - ');
		$this->assertEquals('test1 - test2 - test3 - test4', $bx);

		$bx = PHP::box([1, 2, 3, 4, 5, 6]);
		$this->assertEquals(21, $bx->sum());

		$bx = PHP::box([-2, -2, -2, -3, 5, 6.6, 7.7, 8.8]);
		$this->assertEquals(19.1, $bx->sum());

		$bx->load(['stuff1', 'stuff2'], ['stuff3']);
		$this->assertEquals(PHP::box(['stuff1', 'stuff2', 'stuff3']), $bx);

		$this->assertEquals(
			PHP::box(['t1' => 'test1', 't4' => 'test4']),
			$bx0->extract('t1', 't4')
		);

		$bx = Box::combine(
			[ 'Raz', 'Dva', 'Tri'],
			[  1.1,   2.2,   3.3 ],
		);
		$this->assertEquals(PHP::box(['Raz' => 1.1, 'Dva' => 2.2, 'Tri' => 3.3]), $bx);

		$bx = PHP::box([
			'key1' => 100,
			'key2' => 200,
			'key3' => 300,
			'key4' => 400,
		]);

		$bx = $bx->each(
			fn($val, $key) => !in_array($key, ['key1', 'key3'])
				?[$val, $key]
				:null
		);
		$this->assertEquals(2, $bx->size);
		$this->assertEquals(PHP::box(['key2' => 200, 'key4' => 400]), $bx);

		// Just cloning
		$bx1 = $bx->clone()->each();
		$this->assertNotEquals($bx->obj_id, $bx1->obj_id);
		$this->assertEquals(2, $bx1->size);
		$this->assertEquals(PHP::box(['key2' => 200, 'key4' => 400]), $bx1);

		$bx[] = 'test100500';
		$bx[] = 'test100500';
		$bx[] = 'test100500';
		$bx[] = 'test100500';
		$bx[] = 'test100500';
		$bx[] = 'test100500';

		$this->assertEquals(8, $bx->size);
		$this->assertEquals(3, $bx->toSet()->size);

		$bx->each(fn($val) => [null]);
		$this->assertEquals(8, $bx->size);
		$this->assertEquals(
			PHP::box([
				'key2' => null,
				'key4' => null,
				0 => null,
				1 => null,
				2 => null,
				3 => null,
				4 => null,
				5 => null,
			]),
			$bx
		);

//		$bx->cbKeys([Str::class, 'upper']);
//		$this->assertEquals(8, $bx->size);
//		$this->assertEquals(
//			PHP::box([
//				'KEY2' => null,
//				'KEY4' => null,
//				0 => null,
//				1 => null,
//				2 => null,
//				3 => null,
//				4 => null,
//				5 => null,
//			]),
//			$bx
//		);
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testBoxSumException() {
		$this->expectException(ValueError::class);
		$bx = PHP::box([-2, -2, -2, -3, 'my text that will lead to failure', 6.6, 7.7, 8.8]);
		$this->assertEquals(99, $bx->sum());
	}

	function testStacks() {
		$stack_l = PHP::stack([1, 2, 3, 4], type: 'lifo');
		$stack_f = PHP::stack([1, 2, 3, 4], type: 'fifo');

		// Yes, the string/content representation is no different
		$this->assertEquals("{$stack_l}", "{$stack_f}");

		$stack_f->push('test');
		$stack_l->push('test');

		// Yes again, the string/content representation is no different
		$this->assertEquals("{$stack_l}", "{$stack_f}");

		$l = $stack_l->pop();
		$f = $stack_f->pop();

		$this->assertNotEquals($l, $f);

		$this->assertEquals('test', $l);
		$this->assertEquals(1, $f);

		$this->assertEquals(4, $stack_l->size);
		$this->assertEquals(4, $stack_f->size);

		$l = $stack_l->pop(2);
		$f = $stack_f->pop(2);

		$this->assertEquals(2, $stack_l->size);
		$this->assertEquals(2, $stack_f->size);

		$this->assertEquals('["3","4"]', "{$l}");
		$this->assertEquals('["2","3"]', "{$f}");

		$this->assertEquals('["1","2"]', "{$stack_l}");
		$this->assertEquals('{"2":"4","3":"test"}', "{$stack_f}");

		foreach ($stack_l->walk() as $item) {
			$this->assertNotEmpty($item);
		}

		foreach ($stack_f->walk() as $item) {
			$this->assertNotEmpty($item);
		}

		$this->assertEquals(0, $stack_l->size);
		$this->assertEquals(0, $stack_f->size);
	}

	/**
	 * @covers \spaf\simputils\models\Set
	 * @return void
	 */
	function testSets() {
		$set = PHP::set(['test', 'test', 'test', 'test2', 'test2', 'test3']);

		$this->assertInstanceOf(Set::class, $set);
		$this->assertEquals(3, $set->size);

		$set->exchangeArray(['tree', 'three', 'tree', 'three']);
		$this->assertEquals(2, $set->size);
		$this->assertEquals(['tree', 'three'], $set->toArray());

		$set[] = 'bear';
		$set[] = 'where';
		$set[] = 'where';
		$set[] = 'Bears';
		$set[] = 'Bears';

		$this->assertEquals(5, $set->size);
		$this->assertEquals(['tree', 'three', 'bear', 'where', 'Bears'], $set->toArray());
	}
}
