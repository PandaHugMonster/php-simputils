<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\generic\fixups\FixUpDateTimePrism;

/**
 * Date Prism
 *
 * It holds the reference to the original object of DateTime inside, and extracts Date relevant
 * data.
 *
 * @property-read string $for_system
 * @property-read string $for_user
 */
class Date extends FixUpDateTimePrism {

	#[Property('for_system')]
	protected function getForSystem(): string {
		return $this->_object->getForSystemObj()->format(DT::FMT_DATE);
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		$obj = $this->_object;
		return $obj->format(DateTime::$l10n_user_date_format);
	}
}
