<?php


namespace spaf\simputils\generic;


use spaf\simputils\interfaces\VersionParserInterface;
use spaf\simputils\models\Version;
use spaf\simputils\PHP;
use function is_null;

abstract class BasicVersionParser extends SimpleObject implements VersionParserInterface {

	public static array $build_type_priorities = [
		'DEV' => 0,
		'A' => 1,   'ALPHA' => 1,
		'B' => 2,   'BETA' => 2,
		'RC' => 3,
		'#' => 4,
		'P' => 5,   'PL' => 5,
	];

	public function toString(Version|string $obj): string {
		$obj = static::normalize($obj);

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

	abstract public function greaterThan(Version|string $obj1, Version|string $obj2): bool;

	abstract public function equalsTo(Version|string $obj1, Version|string $obj2): bool;

	public function greaterThanEqual(Version|string $obj1, Version|string $obj2): bool {
		$obj1 = static::normalize($obj1);
		$obj2 = static::normalize($obj2);

		return $this->greaterThan($obj1, $obj2) || $this->equalsTo($obj1, $obj2);
	}

	public function lessThan(Version|string $obj1, Version|string $obj2): bool {
		$obj1 = static::normalize($obj1);
		$obj2 = static::normalize($obj2);

		return !$this->greaterThanEqual($obj1, $obj2);
	}

	public function lessThanEqual(Version|string $obj1, Version|string $obj2): bool {
		$obj1 = static::normalize($obj1);
		$obj2 = static::normalize($obj2);

		return !$this->greaterThan($obj1, $obj2) &&
			($this->lessThan($obj1, $obj2) || $this->equalsTo($obj1, $obj2));
	}

	public static function normalize(
		Version|string|null $item,
		?string $app_name = null
	): ?Version {
		$class = PHP::redef(Version::class);

		if (is_null($item))
			return null;
		if ($item instanceof Version)
			return $item;

		return new $class($item, $app_name);
	}
}
