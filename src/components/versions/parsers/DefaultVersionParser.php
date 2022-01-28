<?php


namespace spaf\simputils\components\versions\parsers;


use spaf\simputils\exceptions\IncorrectVersionFormat;
use spaf\simputils\generic\BasicVersionParser;
use spaf\simputils\models\Version;
use spaf\simputils\Str;

/**
 *
 */
class DefaultVersionParser extends BasicVersionParser {

	/**
	 * @inheritdoc
	 * @throws \spaf\simputils\exceptions\IncorrectVersionFormat
	 */
	public function parse(Version $version_object, ?string $string_version): array {

		$matches = [];

		$_major = $_minor = $_patch = $_prefix = $_postfix = $_type = $_revision = null;
		$statuses = array_keys(static::$build_type_priorities);
		$statuses_bunch = implode('|', $statuses);

		if (filter_var($string_version, FILTER_VALIDATE_INT) !== false) {
			$regexp = '/(?P<major>\d+)/';
			$_minor = 0;
			$_patch = 0;
			preg_match($regexp, $string_version, $matches);
		} else if (filter_var($string_version, FILTER_VALIDATE_FLOAT) !== false) {
			$regexp = '/(?P<major>\d+)\.(?P<minor>\d+)/';
			$_patch = 0;
			preg_match($regexp, $string_version, $matches);
		} else {
			$string_version = Str::upper($string_version);
			if (!empty($version_object->software_name))
				$string_version = str_replace(Str::upper($version_object->software_name), '', $string_version);

			$string_version = preg_replace('/[-_+.]+/', '.', $string_version);
			$string_version = str_replace(' ', '', $string_version);

			$regexp = '/(?P<ltype>'.$statuses_bunch.')?\.?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)\.?(?P<rtype>'.$statuses_bunch.')?\.?(?P<revision>\d+)?/i';

			$res = '';
			for ($i = 0; $i < strlen($string_version); $i++) {
				$symbol_prev = $i > 0?$string_version[$i-1]:null;
				$symbol_current = !empty($string_version[$i])?$string_version[$i]:0;

				$left_side = preg_match('/[A-Z]/', $symbol_current ?? '') && preg_match('/[0-9]/', $symbol_prev ?? '');
				$right_side = preg_match('/[0-9]/', $symbol_current ?? '') && preg_match('/[A-Z]/', $symbol_prev ?? '');

				if ($left_side || $right_side) {
					$res .= '.';
				}

				$res .= $symbol_current;
			}
			preg_match($regexp, $res, $matches);
		}

		if (empty($matches)) {
			throw new IncorrectVersionFormat("Could not parse version string \"{$string_version}\" by \"{$regexp}\"");
		}

		return [
			'major' => $matches['major'] ?? $_major,
			'minor' => $matches['minor'] ?? $_minor,
			'patch' => $matches['patch'] ?? $_patch,
			'prefix' => $matches['prefix'] ?? $_prefix,
			'postfix' => $matches['postfix'] ?? $_postfix,
			'build_type' => $matches['rtype'] ?? $matches['ltype'] ?? $_type,
			'build_revision' => $matches['revision'] ?? $_revision,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function greaterThan(Version|string $obj1, Version|string $obj2): bool {
		$obj1 = static::normalize($obj1);
		$obj2 = static::normalize($obj2);

		$priorities = static::$build_type_priorities;

		if ($obj1->major > $obj2->major) {
			return true;
		} else if ($obj1->major == $obj2->major) {
			////
			if ($obj1->minor > $obj2->minor) {
				return true;
			} else if ($obj1->minor == $obj2->minor) {
				////
				if ($obj1->patch > $obj2->patch) {
					return true;
				} else if ($obj1->patch == $obj2->patch && !empty($obj1->build_type)) {
					// --
					if (empty($obj2->build_type))
						return true;
					else if ($priorities[$obj1->build_type] > $priorities[$obj2->build_type]) {
						return true;
					} else {
						$rev1 = $obj1->build_revision ?? 0;
						$rev2 = $obj2->build_revision ?? 0;
						if ($rev1 > $rev2)
							return true;
					}
					// --
				}
				////
			}
			////
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function equalsTo(Version|string $obj1, Version|string $obj2): bool {
		$obj1 = static::normalize($obj1);
		$obj2 = static::normalize($obj2);

		$priorities = static::$build_type_priorities;
		if ($obj1->major === $obj2->major && $obj1->minor === $obj2->minor && $obj1->patch === $obj2->patch) {
			if (!empty($priorities[$obj1->build_type]) && empty($priorities[$obj2->build_type])) {
				return false;
			} else if (empty($priorities[$obj1->build_type]) && empty($priorities[$obj2->build_type])) {
				return true;
			} else if (!empty($priorities[$obj1->build_type]) && !empty($priorities[$obj2->build_type])) {
				if ($obj1->build_type == $obj2->build_type) {
					$rev1 = $obj1->build_revision ?? 0;
					$rev2 = $obj2->build_revision ?? 0;
					if ($rev1 === $rev2)
						return true;
				}
			}
		}

		return false;
	}
}
