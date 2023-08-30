<?php

namespace spaf\simputils\bin\temp_file;

use spaf\simputils\attributes\markers\Deprecated;

// FIX  Remove before merge

/**
 * @deprecated
 */
#[Deprecated(
	'Just a test Deprecation',
	since: '2.1.6',
	removed: '3.0.0',
)]
class NewOldCl {

	#[Deprecated(
		'Just BeBeBe Deprecation',
		since: '1.5.0',
		removed: '1.2.0',
	)]
	static function bebebe() {

	}

	static function nopnopnop() {

	}

}

/**
 * @deprecated
 */
#[Deprecated(
	'Just a test Deprecation 2',
	'RePlAcEmEnT',
	since: '1.2.0',
	removed: '2.0.0',
)]
class NewOldCl2 {

	#[Deprecated(
		'Just BeBeBe Deprecation',
		since: '1.3.0',
		removed: '1.2.0',
	)]
	static function bebebe() {

	}

	static function nopnopnop() {

	}

}

/**
 * @deprecated
 */
#[Deprecated(
	'Just a test Deprecation 2',
	since: '1.2.0',
	removed: '4.0.0',
)]
class NewOldCl3 {

	static function bebebe() {

	}

	static function nopnopnop() {

	}

}
