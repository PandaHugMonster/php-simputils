<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\markers\Deprecated;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\Box;
use spaf\simputils\models\UrlObject;

/**
 * @property-read ?string $protocol
 */
abstract class BasicSchemeProcessor extends SimpleObject {

	static ?string $default_protocol = null;

	/**
	 * Returns array of supported protocol names
	 *
	 * @deprecated
	 * @return Box|string[]|array|null
	 *
	 * @noinspection PhpAttributeCanBeAddedToOverriddenMemberInspection*
	 */
	#[Deprecated(
		'Wrong naming "protocol" instead of commonly used "scheme"',
		'\spaf\simputils\generic\BasicProtocolProcessor::supportedSchemes'
	)]
	static function supportedProtocols() {
		return null;
	}

	/**
	 * Returns array of supported schemes names
	 *
	 * TODO Mark it as abstract when deprecated previous code is removed
	 *
	 * @return Box|string[]|array|null
	 */
	static function supportedSchemes() {
		return static::supportedProtocols();
	}

	abstract static function parse(UrlCompatible|string $value);

	abstract static function generateForSystem(UrlObject $url): string;
	abstract static function generateForUser(UrlObject $url): string;
	abstract static function generateRelative(UrlObject $url): string;

	// FIX
//
//	#[Property(type: 'get')]
//	protected ?string $_protocol = null;
//
//	function __construct(string $protocol) {
//		$this->_protocol = $protocol;
//	}
//
//	abstract function parse(UrlCompatible|string|Box|array $value, bool $is_preparsed = false, $data = null);
//
//	abstract function generateForSystem($host, $path, $params, $data): string;
//
//	abstract function generateForUser($host, $path, $params, $data): string;
//
//	abstract function generateRelative($host, $path, $params, $data): string;

//	function __toString(): string {
//		return PHP::objToNaiveString($this, ['protocol' => $this->_protocol]);//@codeCoverageIgnore
//	}
}
