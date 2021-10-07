<?php

namespace spaf\simputils\models;


use spaf\simputils\components\SimpleObject;
use spaf\simputils\interfaces\VersionParserInterface;
use spaf\simputils\versions\DefaultVersionParser;

/**
 * Version object class
 *
 * Comfortable way to operate with version information.
 *
 * It parses version (which parser to use is flexible as well), and stores it. Allows comparing versions, sort them, etc.
 *
 * Example:
 * ```php
 *  $version = new Version('13.34.56', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 12.34.56
 *  print_r($version);
 *  // Outputs like (as a str):
 *  //      spaf\simputils\Version Object
 *  //      (
 *  //          [software_name] => My app
 *  //          [parsed_version] => 12.34.56
 *  //      )
 *
 *  $version = Version::new('My APP RC13.34.56', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 13.34.56-RC
 *
 *  $version = Version::new('My APP 13.34.56RC', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 13.34.56-RC
 *
 *  $version = Version::new(' 13.34.56RC55 ', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 13.34.56-RC55
 *
 *  $version = Version::new('13.34.56F99', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 13.34.56
 *
 *  $version = Version::new('13.34.56F99', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 13.34.56
 *
 *  $version = Version::new('20020611', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 20020611.0.0
 *
 *  $version = Version::new('15', 'My app');
 *  echo "$version\n";
 *  // Outputs like (as a str): 20020611.0.0
 *
 *  $version = Version::new('SOMERUBBISHHERE--15.12.0');
 *  echo "$version\n";
 *  // Outputs like (as a str): 15.12.0
 *
 * ```
 *
 * @see https://www.php.net/manual/ru/function.version-compare.php The basic logic was inspired by this method
 * @see https://semver.org/ The key functionality is directed to Semantic Versioning
 * @package spaf\simputils
 */
class Version extends SimpleObject {

	public ?string $software_name = null;

	public static string $default_parser_class = DefaultVersionParser::class;
	public static bool $debug_include_orig = false;
	protected ?VersionParserInterface $_parser = null;
	public ?int $major = null;
	public ?int $minor = null;
	public ?int $patch = null;

	public ?string $build_type = null;
	public mixed $build_revision = null;

	public ?string $prefix = null;
	public ?string $postfix = null;
	public ?string $non_standard = null;
	protected ?string $original_value = null;
	protected null|string|bool $original_strict = null;

	public function getParser() {
		if (empty($this->_parser))
			$this->_parser = new static::$default_parser_class();
		return $this->_parser;
	}

	public function setParser($val) {
		$this->_parser = $val;
	}

	/**
	 * Version constructor.
	 *
	 * @param string|null $version
	 * @param string|null $software_name
	 * @param \spaf\simputils\interfaces\VersionParserInterface|null $parser
	 */
	public function __construct(?string $version = null, ?string $software_name = null, ?VersionParserInterface $parser = null) {
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

	public function __toString(): string {
		return $this->parser->toString($this);
	}

	public function equalsTo(Version|string $obj): bool {
		return $this->parser->equalsTo($this, $obj);
	}

	public function greaterThan(Version|string $obj): bool {
		return $this->parser->greaterThan($this, $obj);
	}

	public function lessThan(Version|string $obj): bool {
		return $this->parser->lessThan($this, $obj);
	}

	public function greaterThanEqual(Version|string $obj): bool {
		return $this->parser->greaterThanEqual($this, $obj);
	}

	public function lessThanEqual(Version|string $obj): bool {
		return $this->parser->lessThanEqual($this, $obj);
	}

	/**
	 * "equal_to" shortcut
	 *
	 * @param \spaf\simputils\models\Version|string $obj
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
	 * @param \spaf\simputils\models\Version|string $obj
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
	 * @param \spaf\simputils\models\Version|string $obj
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
	 * @param \spaf\simputils\models\Version|string $obj
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
	 * @param \spaf\simputils\models\Version|string $obj
	 *
	 * @return bool
	 * @see lessThanEqual()
	 */
	public function lte(Version|string $obj): bool {
		return $this->lessThanEqual($obj);
	}

	/**
	 * Object representation for debug purposes
	 *
	 * @return array|null
	 */
	public function __debugInfo(): ?array {
		$res = [
			'software_name' => $this->software_name,
			'parsed_version' => strval($this),
		];

		if (static::$debug_include_orig)
			$res['orig_version'] = strval($this->original_value);

		return $res;
	}

}