<?php

namespace spaf\simputils\models;


use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\Extract;
use spaf\simputils\attributes\Property;
use spaf\simputils\components\versions\parsers\DefaultVersionParser;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\VersionParserInterface;
use spaf\simputils\traits\RedefinableComponentTrait;
use function json_encode;

/**
 * Version object class
 *
 * Comfortable way to operate with version information.
 *
 * It parses version (which parser to use is flexible as well), and stores it.
 * Allows comparing versions, sort them, etc.
 *
 * Example:
 * ```php
 *      $version = new Version('13.34.56', 'My app');
 *      echo "{$version} / type: {$version->obj_type}\n";
 *      // Outputs like (as a str): 13.34.56 / type: spaf\simputils\models\Version
 *      print_r($version);
 *      // Outputs like (as a str):
 *      //      spaf\simputils\models\Version Object
 *      //      (
 *      //          [software_name] => My app
 *      //          [parsed_version] => 13.34.56
 *      //      )
 *
 *      $version = new Version('My APP RC13.34.56', 'My app');
 *      echo "$version\n";
 *      // Outputs like (as a str): 13.34.56-RC
 *
 *      $version = new Version('My APP 13.34.56RC', 'My app');
 *      echo "$version\n";
 *      // Outputs like (as a str): 13.34.56-RC
 *
 *      $version = new Version(' 13.34.56RC55 ', 'My app');
 *      echo "$version\n";
 *      // Outputs like (as a str): 13.34.56-RC55
 *
 *      $version = new Version('13.34.56F99', 'My app');
 *      echo "$version\n";
 *      // Outputs like (as a str): 13.34.56
 *
 *      $version = new Version('13.34.56F99', 'My app');
 *      echo "$version\n";
 *      // Outputs like (as a str): 13.34.56
 *
 *      $version = new Version('20020611', 'My app');
 *      echo "$version\n";
 *      // Outputs like (as a str): 20020611.0.0
 *
 *      $version = new Version('15', 'My app');
 *      echo "$version\n";
 *      // Outputs like (as a str): 15.0.0
 *
 *      $version = new Version('SOMERUBBISHHERE--15.12.0');
 *      echo "$version\n";
 *      // Outputs like (as a str): 15.12.0
 *
 * ```
 *
 * TODO Replace with interface
 * TODO In future versions implement "format" method like for dates
 * FIX  Implement option to display with software name (but compatibly with parsing)
 * @property \spaf\simputils\generic\BasicVersionParser $parser
 *
 * @see https://www.php.net/manual/en/function.version-compare.php
 *      The basic logic was inspired by this method
 * @see https://semver.org/ The key functionality is directed to Semantic Versioning
 * @package spaf\simputils\models
 */
class Version extends SimpleObject {
	use RedefinableComponentTrait;

	/**
	 * Default version parser is used for newly created `Version` objects
	 *
	 * @var string
	 */
	public static string $default_parser_class = DefaultVersionParser::class;

	/**
	 * If true - then debug print out will include original version string
	 *
	 * @var bool
	 */
	public static bool $debug_include_orig = false;

	/**
	 * Software name property (In the most cases users can skip it, though it's strongly recommended
	 * to use everytime)
	 * @var string|null
	 */
	public ?string $software_name = null;

	/**
	 * Contains the assigned parser during object creation By default class from
	 * {@see static::$default_parser_class} is used, but it can be redefined in per case basis
	 * (even during creation of `Version` object)
	 *
	 * @var VersionParserInterface|null
	 */
	#[DebugHide]
	#[Extract(false)]
	protected ?VersionParserInterface $_parser = null;

	/**
	 * Contains the major part of the version
	 *
	 * @var int|mixed|null
	 */
	public ?int $major = null;

	/**
	 * Contains the minor part of the version
	 *
	 * @var int|mixed|null
	 */
	public ?int $minor = null;

	/**
	 * Contains the patch part of the version
	 *
	 * @var int|mixed|null
	 */
	public ?int $patch = null;

	/**
	 * Contains build type if specified/parsed correctly.
	 *
	 * The build types recognisable by the parsers.
	 * The default parser recognises the following types (letter case ignored):
	 *
	 *  1.  "DEV"
	 *  2.  "A" or "ALPHA"
	 *  3.  "B" or "BETA"
	 *  4.  "RC"
	 *  5.  "#"
	 *  6.  "P" or "PL"
	 *
	 * The priority is on the same order, more info you can find here
	 * {@see \spaf\simputils\generic\BasicVersionParser}
	 *
	 * @var string|mixed|null
	 */
	public ?string $build_type = null;
	/**
	 * Build revision, is considered only when build type is specified Example:
	 * "RC2", "A3", etc.
	 *
	 * @var mixed|null
	 */
	public mixed $build_revision = null;

	/**
	 * @todo Undone
	 * @var string|mixed|null
	 */
	public ?string $prefix = null;
	/**
	 * @todo Undone
	 * @var string|mixed|null
	 */
	public ?string $postfix = null;
	/**
	 * @todo Undone
	 * @var string|null
	 */
	public ?string $non_standard = null;
	/**
	 * Original string that was parsed
	 *
	 * @var string|null
	 */
	protected ?string $original_value = null;
	/**
	 * @todo clarify
	 * @var string|bool|null
	 */
	protected null|string|bool $original_strict = null;

	/**
	 * Getter for $parser
	 *
	 * @return mixed|\spaf\simputils\interfaces\VersionParserInterface|null
	 */
	#[Property('parser')]
	public function getParser() {
		if (empty($this->_parser))
			$this->_parser = new static::$default_parser_class();
		return $this->_parser;
	}

	/**
	 * Setter for $parser
	 *
	 * @param mixed $val Value to assign to $parser property
	 *
	 * @return void
	 */
	#[Property('parser')]
	public function setParser($val) {
		$this->_parser = $val;
	}

	/**
	 * Version constructor.
	 *
	 * @param string|null                 $version       Version string to parse
	 * @param string|null                 $software_name Software name
	 * @param VersionParserInterface|null $parser        Custom parser for the new object (must be
	 *                                                   parser-object and not a class-string)
	 */
	public function __construct(
		?string $version = null,
		?string $software_name = null,
		?VersionParserInterface $parser = null
	) {
		$this->original_value = $version;
		$this->software_name = $software_name;
		if (!empty($parser))
			$this->parser = $parser;

		$data = $this->parser->parse($this, $version);
		$this->major = $data['major'] ?? null;
		$this->minor = $data['minor'] ?? null;
		$this->patch = $data['patch'] ?? null;
		$this->prefix = $data['prefix'] ?? null;
		$this->postfix = $data['postfix'] ?? null;
		$this->build_type = $data['build_type'] ?? null;
		$this->build_revision = $data['build_revision'] ?? null;
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		// IMP FIX  There is a problem here if "parser" property is used
		return $this->getParser()
			?$this->getParser()->toString($this)
			:'';
	}

	/**
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 */
	public function equalsTo(Version|string $obj): bool {
		return $this->parser->equalsTo($this, $obj);
	}

	/**
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 */
	public function greaterThan(Version|string $obj): bool {
		return $this->parser->greaterThan($this, $obj);
	}

	/**
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 */
	public function lessThan(Version|string $obj): bool {
		return $this->parser->lessThan($this, $obj);
	}

	/**
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 */
	public function greaterThanEqual(Version|string $obj): bool {
		return $this->parser->greaterThanEqual($this, $obj);
	}

	/**
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 */
	public function lessThanEqual(Version|string $obj): bool {
		return $this->parser->lessThanEqual($this, $obj);
	}

	/**
	 * "equal_to" shortcut
	 *
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 * @see equal_to()
	 */
	public function e(Version|string $obj): bool {
		return $this->equalsTo($obj);
	}

	/**
	 * "greater_than" shortcut
	 *
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 * @see greaterThan()
	 */
	public function gt(Version|string $obj): bool {
		return $this->greaterThan($obj);
	}

	/**
	 * "greater_than" shortcut
	 *
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 * @see greaterThan()
	 */
	public function lt(Version|string $obj): bool {
		return $this->lessThan($obj);
	}

	/**
	 * "greater_than_equal" shortcut
	 *
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 * @see greaterThanEqual()
	 */
	public function gte(Version|string $obj): bool {
		return $this->greaterThanEqual($obj);
	}

	/**
	 * "less_than_equal" shortcut
	 *
	 * @param Version|string $obj Right side object of the comparison
	 *
	 * @return bool
	 * @see lessThanEqual()
	 */
	public function lte(Version|string $obj): bool {
		return $this->lessThanEqual($obj);
	}

	public function toJson(?bool $pretty = null, bool $with_class = false): string {
		return json_encode("{$this}");
	}

	/**
	 * Object representation for debug purposes
	 *
	 * @return array|null
	 */
	public function __debugInfo(): array {
		$res = [
			'software_name' => $this->software_name,
			'parsed_version' => strval($this),
		];

		if (static::$debug_include_orig)
			$res['orig_version'] = strval($this->original_value);

		return $res;
	}

	public static function redefComponentName(): string {
		return InitConfig::REDEF_VERSION;
	}
}
