<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\DT;
use spaf\simputils\models\Date;
use spaf\simputils\models\Time;

/**
 * @covers \spaf\simputils\models\Date
 * @covers \spaf\simputils\models\Time
 * @covers \spaf\simputils\generic\BasicPrism
 * @covers \spaf\simputils\generic\fixups\FixUpDateTimePrism
 *
 * @uses \spaf\simputils\DT
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\models\DateTime
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\PropertiesTrait::__get
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\traits\PropertiesTrait::_simpUtilsPrepareProperty
 * @uses \spaf\simputils\traits\PropertiesTrait::getAllTheLastMethodsAndProperties
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getObjId
 * @uses \spaf\simputils\traits\MetaMagic::createDummy
 *
 */
class PrismsTest extends TestCase {

	function testBasics() {
		$dt = DT::ts('2020-03-04 05:06:07');
		$prism_d = $dt->date;
		$prism_t = $dt->time;

		$this->assertEquals('2020-03-04 05:06:07.000000', "{$prism_d->for_system}");
		$this->assertEquals('2020-03-04 05:06:07.000000', "{$prism_t->for_system}");
		$this->assertEquals($dt, $prism_d->object);
		$this->assertEquals($dt, $prism_t->object);

		$dt2 = DT::ts('2020-03-04 05:06:07');

		$this->assertNotEquals($dt2->obj_id, $dt->obj_id);

		$prism_d2 = Date::wrap($dt2);
		$prism_t2 = Time::wrap($dt2);

		$this->assertInstanceOf(Date::class, $prism_d2);
		$this->assertInstanceOf(Time::class, $prism_t2);

		$this->assertEquals('2020-03-04 05:06:07.000000', "{$prism_d2->for_system}");
		$this->assertEquals('2020-03-04 05:06:07.000000', "{$prism_t2->for_system}");
	}
}
