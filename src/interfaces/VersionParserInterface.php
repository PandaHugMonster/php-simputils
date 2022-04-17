<?php


namespace spaf\simputils\interfaces;



use spaf\simputils\models\Version;

interface VersionParserInterface {

	/**
	 * The main parsing method
	 *
	 * @param Version $version_object Version object
	 * @param ?string $string_version String representation of version that should be parsed
	 *
	 * @return array
	 */
	public function parse(Version $version_object, ?string $string_version): array;

	/**
	 * Logic of ">"
	 *
	 * @param Version|string $obj1 Left Version object
	 * @param Version|string $obj2 Right Version object
	 *
	 * @return bool
	 */
	public function greaterThan(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of ">="
	 * @param Version|string $obj1 Left Version object
	 * @param Version|string $obj2 Right Version object
	 *
	 * @return bool
	 */
	public function greaterThanEqual(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of "=="
	 *
	 * @param Version|string $obj1 Left Version object
	 * @param Version|string $obj2 Right Version object
	 *
	 * @return bool
	 */
	public function equalsTo(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of "<"
	 *
	 * @param Version|string $obj1 Left Version object
	 * @param Version|string $obj2 Right Version object
	 *
	 * @return bool
	 */
	public function lessThan(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Logic of "<="
	 *
	 * @param Version|string $obj1 Left Version object
	 * @param Version|string $obj2 Right Version object
	 *
	 * @return bool
	 */
	public function lessThanEqual(Version|string $obj1, Version|string $obj2): bool;

	/**
	 * Turn version object to string
	 *
	 * @param Version|string $obj Version that should be turned into a string
	 *
	 * @return string
	 */
	public function toString(Version|string $obj): string;
}
