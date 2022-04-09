<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Temperature;

/**
 *
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____PrepareProperty
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::getAllTheLastMethodsAndProperties
 * @uses \spaf\simputils\traits\SimpleObjectTrait::simpUtilsGetValidator
 *
 */
class CommonPurposeModelsTest extends TestCase {

	/**
	 * @covers \spaf\simputils\models\Temperature
	 *
	 * @return void
	 */
	function testTemperatureClass() {
		$obj = new Temperature(123);

		$this->assertEquals('123', $obj->celsius);
		$this->assertEquals('253.4', $obj->fahrenheit);
		$this->assertEquals('396.15', $obj->kelvin);

		$this->assertEquals('123 C', $obj->for_system);
		$this->assertEquals('123 C°', $obj->for_user);

		Temperature::$disable_degree_symbol = true;
		$this->assertEquals('123', $obj->for_user);
		Temperature::$disable_degree_symbol = false;

		$this->assertEquals(
			-0.67,
			Temperature::convert(255, Temperature::UNIT_KELVIN,
				Temperature::UNIT_FAHRENHEIT)
		);
		$this->assertEquals(
			123.89,
			Temperature::convert(255, Temperature::UNIT_FAHRENHEIT,
				Temperature::UNIT_CELSIUS)
		);

		Temperature::$auto_round = false;
		$this->assertEquals(
			-18.15,
			Temperature::convert(255, Temperature::UNIT_KELVIN,
				Temperature::UNIT_CELSIUS)
		);

		Temperature::$auto_round = 15;
		$this->assertEquals(
			397.03888888889,
			Temperature::convert(255, Temperature::UNIT_FAHRENHEIT,
				Temperature::UNIT_KELVIN)
		);

		$this->assertEquals('°', $obj->symbol(no_unit: true));

	}
}
