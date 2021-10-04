<?php


namespace spaf\simputils\components;


use spaf\simputils\interfaces\VersionParserInterface;
use spaf\simputils\models\Version;

abstract class BasicVersionParser implements VersionParserInterface {

	public static array $build_type_priorities = [
		'DEV' => 0,
		'A' => 1,   'ALPHA' => 1,
		'B' => 2,   'BETA' => 2,
		'RC' => 3,
		'#' => 4,
		'P' => 5,   'PL' => 5,
	];

	public function toString(Version $obj): string {
		$res = $obj->major.'.'.$obj->minor.'.'.$obj->patch;

		$check_1 = !empty($obj->build_type);
		$check_2 = !empty($obj->build_revision);
		if ($check_1 || $check_2) {
			$res .= $obj->build_type !== '#'?'-':'';

			if ($check_1)
				$res .= $obj->build_type;

			if ($check_2)
				$res .= $obj->build_revision;
		}

		return $res;
	}

	abstract public function parse(Version $version_object, ?string $string_version): array;

	abstract public function greaterThan(Version $obj1, Version $obj2): bool;
	abstract public function equalsTo(Version $obj1, Version $obj2): bool;

	public function greaterThanEqual(Version $obj1, Version $obj2): bool {
		return $this->greaterThan($obj1, $obj2) || $this->equalsTo($obj1, $obj2);
	}

	public function lessThan(Version $obj1, Version $obj2): bool {
		return !$this->greaterThanEqual($obj1, $obj2);
	}

	public function lessThanEqual(Version $obj1, Version $obj2): bool {
		return !$this->greaterThan($obj1, $obj2) &&
			($this->lessThan($obj1, $obj2) || $this->equalsTo($obj1, $obj2));
	}
}