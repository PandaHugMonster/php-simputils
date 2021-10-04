<?php


namespace spaf\simputils\interfaces;



use spaf\simputils\models\Version;

interface VersionParserInterface {

	public function parse(Version $version_object, ?string $string_version): array;

	public function greaterThan(Version $obj1, Version $obj2): bool;
	public function greaterThanEqual(Version $obj1, Version $obj2): bool;
	public function equalsTo(Version $obj1, Version $obj2): bool;
	public function lessThan(Version $obj1, Version $obj2): bool;
	public function lessThanEqual(Version $obj1, Version $obj2): bool;

	public function toString(Version $obj): string;

}