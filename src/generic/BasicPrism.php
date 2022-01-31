<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\Property;

/**
 * Basic Prism class
 *
 * Prism in the context of the framework means a special "sub-object" that contains main object
 * for reference, but provides modified meaning.
 *
 * Simpler - it's a small proxy objects that provides similar functionality to the target class.
 *
 * Examples of Prisms are: `Date` and `Time` prism classes which target class is `DateTime`
 *
 * Prism classes/objects are satellites of the target classes/objects. You should not create
 * prism classes, if there is no target class for it.
 *
 * @property-read ?object $object
 */
abstract class BasicPrism extends SimpleObject {

	protected $_object;

	#[Property('object')]
	protected function getObject() {
		return $this->_object;
	}

	public function init($target_object) {
		$this->_object = $target_object;
	}

	public static function wrap(object $target_object) {
		$self = static::createDummy();
		$self->init($target_object);
	}
}
