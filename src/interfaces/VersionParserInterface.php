<?php


namespace spaf\simputils\interfaces;



use spaf\simputils\models\Version;

interface VersionParserInterface {

	/**
	 * The main parsing method
	 *
	 * @param \spaf\simputils\models\Version $version_object
	 * @param string|null $string_version
	 *
	 * @return array
	 */
	public function parse(Version $version_object, ?string $string_version): array;

	/**
	 * Logic of ">"
	 *
	 * @param \spaf\simputils\models\Version|string $obj1
	 * @param \spaf\simputils\models\Version|string $obj2
	 *
	 * @return bool
	 */
	public function greaterThan(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of ">="
	 * @param \spaf\simputils\models\Version|string $obj1
	 * @param \spaf\simputils\models\Version|string $obj2
	 *
	 * @return bool
	 */
	public function greaterThanEqual(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of "=="
	 *
	 * @param \spaf\simputils\models\Version|string $obj1
	 * @param \spaf\simputils\models\Version|string $obj2
	 *
	 * @return bool
	 */
	public function equalsTo(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of "<"
	 *
	 * @param \spaf\simputils\models\Version|string $obj1
	 * @param \spaf\simputils\models\Version|string $obj2
	 *
	 * @return bool
	 */
	public function lessThan(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of "<="
	 * @param \spaf\simputils\models\Version|string $obj1
	 * @param \spaf\simputils\models\Version|string $obj2
	 *
	 * @return bool
	 */
	public function lessThanEqual(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Turn version object to string
	 *
	 * @param \spaf\simputils\models\Version|string $obj
	 *
	 * @return string
	 */
	public function toString(Version|string $obj): string;

}