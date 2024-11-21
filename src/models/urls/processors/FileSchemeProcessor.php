<?php

namespace spaf\simputils\models\urls\processors;

use spaf\simputils\generic\BasicSchemeProcessor;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\UrlObject;
use spaf\simputils\PHP;

class FileSchemeProcessor extends BasicSchemeProcessor {

	const SCHEME_FILE = 'file';

	static function supportedSchemes() {
		return PHP::box([
			static::SCHEME_FILE,
		]);
	}

	static function parse(UrlCompatible|string $value) {
		// TODO: Implement parse() method.
	}

	static function generateForSystem(UrlObject $url): string {
		// TODO: Implement generateForSystem() method.
	}

	static function generateForUser(UrlObject $url): string {
		// TODO: Implement generateForUser() method.
	}

	static function generateRelative(UrlObject $url): string {
		// TODO: Implement generateRelative() method.
	}
}
