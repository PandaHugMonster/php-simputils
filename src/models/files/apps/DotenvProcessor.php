<?php

namespace spaf\simputils\models\files\apps;

use spaf\simputils\generic\BasicDotEnvCommentExt;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\models\files\apps\settings\DotEnvSettings;
use function spaf\simputils\basic\pd;

/**
 * DotEnv data processor
 *
 *
 * @package spaf\simputils\models\files\apps
 */
class DotenvProcessor extends TextProcessor {

	public static function defaultProcessorSettings(): mixed {
		return new DotEnvSettings();
	}

	public static function getContent(mixed $stream, ?BasicResource $file = null): mixed {
		$s = static::getSettings($file);
		$content = parent::getContent($stream, $file);
		$lines = explode("\n", $content);
		$res = [];
		foreach ($lines as $i => $line) {
			if (empty($line)) {
				continue;
			}
			$line = trim($line);
			if ($line[0] === '#') {
				// TODO Comment-extension processing must happen here!
				continue;
			}

			[$key, $val] = explode('=', $line, 2);
			$res[trim($key)] = trim($val);
			// FIX  The same line comment - fix it, and keep in mind that sharp symbol can be inside
			//      of the value
			// FIX  + make name normalization to avoid weird symbols being incorporated into
			//      the name. (Maybe permit dot? if it is allowed by the bash processing)
			// FIX  Implement clearing of "export ..." stuff if present
			// FIX  Implement DotEnv functionality for $_ENV etc...
		}
		pd($lines, $res);
		return $res;
	}

	/**
	 * Setting content of the file
	 *
	 * NOTE Due to some flexibility, current mechanisms might not be fully efficient (maybe will be
	 *      fixed in the future!)
	 *
	 * @param mixed          $stream Stream/Pointer/FileDescriptor/Path etc.
	 * @param mixed          $data   Data to store
	 * @param ?BasicResource $file   File instance
	 *
	 * @throws \Exception Error
	 */
	public static function setContent(mixed $stream, $data, ?BasicResource $file = null): void {
		$lines = [];
		/** @var DotEnvSettings $s */
		$s = static::getSettings($file);

		foreach ($data as $name => $value) {
			if (is_numeric($name)) {
				if ($value instanceof BasicDotEnvCommentExt) {
					$value = "{$value}";
				} else {
					$value = '# '.str_replace("\n", "\n# ", $value);
				}

				$lines[] = $value;
			} else {
				$lines[] = $s->normalizeName($name).'='.$s->normalizeValue($value);
			}
		}

		$res = implode("\n", $lines);
		parent::setContent($stream, $res, $file);
	}
}
