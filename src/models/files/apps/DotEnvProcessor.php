<?php

namespace spaf\simputils\models\files\apps;

use spaf\simputils\exceptions\DotEnvCommentExtensionProblem;
use spaf\simputils\generic\BasicDotEnvCommentExt;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\models\files\apps\settings\DotEnvSettings;
use spaf\simputils\special\dotenv\ExtTypeHint;
use spaf\simputils\Str;
use function spaf\simputils\basic\pr;
use function str_replace;
use function trim;

/**
 * DotEnv data processor
 *
 * TODO Prepare detailed specification page for implemented DotEnv parsing/generation
 *
 * IMP  Even though parser might be fine with some inconvenient values, but this behaviour
 *      can change, and is not conventional. so please always follow the specification.
 *      Don't forget escaping internal quote signs!
 *
 * NOTE If you will encounter any parsing problems, or the parsing is done wrong, or even you
 *      think that parsing could be improved - please make an issue here:
 *      https://github.com/PandaHugMonster/php-simputils/issues
 *
 * TODO Multilines - are not supported yet. They will be or will be not introduced after
 *      the specification is finished.
 *
 * @package spaf\simputils\models\files\apps
 */
class DotEnvProcessor extends TextProcessor {

	public static function defaultProcessorSettings(): mixed {
		return new DotEnvSettings();
	}

	/**
	 * @param mixed          $fd   File descriptor
	 * @param ?BasicResource $file Resource/File object
	 *
	 * TODO Yes, lots of `trim()`s, yes it's sub-optimal probably! Subject for improvement.
	 *
	 * @return array|null
	 */
	public function getContent(mixed $fd, ?BasicResource $file = null): ?array {
		$s = static::getSettings($file, $this->_default_settings);
		/** @var DotEnvSettings $s */
		$content = parent::getContent($fd, $file);
		$lines = explode("\n", $content);
		$res = [];
		foreach ($lines as $line) {
			if (Str::contains($line, "\r")) {
				pr("Line contains \"\\r\" symbol, trimmed.");
			}

			$line = trim($line);

			if (empty($line)) {
				continue;
			}

			if ($line[0] === '#') {
				// TODO Comment-extension processing must happen here!

				if ($s->show_comments) {
					// TODO Temporary!
					$res[] = "$line";
				}
				continue;
			}

			[$key, $val] = explode('=', $line, 2);

			$val = trim($val);
			if (empty($val)) {
				continue;
			}
			$is_pre_quoted = in_array($val[0], ['"', "'"]);

			if (!$is_pre_quoted) {
				$_exploded = explode('#', $val, 2);
				[$val, $comment] = count($_exploded) > 1
					?$_exploded
					:[$val, null];
				$val = trim($val);
			} else {
				$m = [];
				$quote = $val[0];
				/** @noinspection RegExpRedundantEscape */
				preg_match(
					"/\\{$quote}.*[^\\\]?\\{$quote}/", $val, $m
					// "/\\{$val[0]}.*[^\\\]?\\{$val[0]}/", $val, $m, PREG_OFFSET_CAPTURE
				);
				$val = trim($m[0], "{$quote} \n\r\t\v\0");
			}

			$res[$s->normalizeName($key)] = $val;
			// TODO Implement clearing of "export ..." stuff if present
			// TODO Implement DotEnv functionality for $_ENV etc...
		}
		return $res;
	}

	/**
	 * Setting content of the file
	 *
	 * NOTE Due to some flexibility, current mechanisms might not be fully efficient (maybe will be
	 *      fixed in the future!)
	 *
	 * @param mixed          $fd   Stream/Pointer/FileDescriptor/Path etc.
	 * @param mixed          $data Data to store
	 * @param ?BasicResource $file File instance
	 *
	 * @throws \spaf\simputils\exceptions\DotEnvCommentExtensionProblem Dot Env comment extension
	 *                                                                  problem
	 */
	public function setContent(mixed $fd, mixed $data, ?BasicResource $file = null): void {
		$lines = [];
		/** @var DotEnvSettings $s */
		$s = static::getSettings($file, $this->_default_settings);

		foreach ($data as $name => $value) {
			if (is_numeric($name)) {
				if ($value instanceof BasicDotEnvCommentExt) {
					$value = "{$value}";
				} else {
					$value = "#\t".str_replace("\n", "\n# ", "{$value}");
				}

				$lines[] = $value;
			} else {
				if ($s->auto_type_hinting && !$value instanceof BasicDotEnvCommentExt) {
					$value = ExtTypeHint::wrap($value);
				}

				if ($value instanceof BasicDotEnvCommentExt) {
					if ($value->getPrefix() !== BasicDotEnvCommentExt::PREFIX_ROW) {
						throw new DotEnvCommentExtensionProblem(
							'Comment-extensions value-wrappers are allowed only for '.
							'"PREFIX_ROW" type.'
						);
					}
					$lines[] = "$value";
					$value = $value->value;
				}
				$lines[] = $s->normalizeName($name).'='.$s->normalizeValue($value);
			}
		}

		$res = implode("\n", $lines);
		parent::setContent($fd, $res, $file);
	}
}
