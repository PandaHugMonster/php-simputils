<?php
namespace spaf\simputils\models\files\apps\settings;

use Exception;
use spaf\simputils\generic\SimpleObject;

/**
 * FIX  Add strict mode at some point
 */
class DotEnvSettings extends SimpleObject {

	const LETTER_CASE_NONE = null;
	const LETTER_CASE_UPPER = 'upper';
	const LETTER_CASE_LOWER = 'lower';

	/**
	 * @var ?string $enforce_letter_case Just transforms the key names into lower or upper case.
	 *                                   If set to null or `DotEnvSettings::LETTER_CASE_NONE` -
	 *                                   then enforcing is disabled and the letter case
	 *                                   will be as is.
	 */
	public ?string $enforce_letter_case = self::LETTER_CASE_UPPER;

	/**
	 * @var bool $collapse_excessive_underscores Will collapse 2 or more consecutive underscores
	 *                                           into 1 if set to true (default), otherwise would
	 *                                           not collapse those.
	 */
	public bool $collapse_excessive_underscores = true;

	/**
	 * @var ?string $always_wrap_values Always wrapping values into quote symbol specified.
	 *                                  It would be ignored if the value having first and last
	 *                                  symbol of double-quote or single-quote (both must be
	 *                                  matching).
	 *                                  For example: $a = "\"Test Me\"" would not be additionally
	 *                                  wrapped. It's done like this so you could control
	 *                                  quote-wrapping by yourself in place. If set to null then
	 *                                  ignored always, and auto-wrapping would not happen in any
	 *                                  case.
	 */
	public ?string $always_wrap_values = '"';

	/**
	 * Parameter name normalizer
	 *
	 * 1.  If activated will make upper/lower case (or ignored if deactivated)
	 * 2.  Will remove all non-permitted symbols for a dotenv parameter name
	 * 3.  Will replace all dashes and spaces (after trimming) to underscores
	 *
	 * **Important:** By default it would collapse multiple occurrences of final underscores,
	 * this behaviour could be adjusted with `$collapse_excessive_underscores` setting.
	 * In any case it would trim (in start and end of the string) excessive spaces before turning
	 * those into underscores
	 *
	 * Examples (Completely default settings):
	 * ```
	 *     " my special--non-special texty" would turn into "MY_SPECIAL_NON_SPECIAL_TEXTY"
	 *     "CouldYoU_PleaseTeLl_mE-smthg" would turn into "COULDYOU_PLEASETELL_ME_SMTHG"
	 *     " -MY - VERY  special TexT- " would turn into "_MY_VERY_SPECIAL_TEXT_"
	 * ```
	 *
	 * Examples (considered default behaviour with upper cases but with
	 *          `$collapse_excessive_underscores` set to false):
	 * ```
	 *     " my special--non-special texty" would turn into "MY_SPECIAL__NON_SPECIAL_TEXTY"
	 *     "CouldYoU_PleaseTeLl_mE-smthg" would turn into "COULDYOU_PLEASETELL_ME_SMTHG"
	 *     " -MY - VERY  special TexT- " would turn into "_MY___VERY__SPECIAL_TEXT_"
	 * ```
	 *
	 * NOTE **Important:** Do not put first symbol as a digit (first symbol after clearing). If you
	 *      will have first position as a digit - then the whole name will be prefixed with "_".
	 *      Params and variables usually is not permitted to start from digits!
	 *
	 * TODO Implement optional Strict Mode with exceptions (And maybe set it as default one)
	 *
	 * @param string $name Parameter/Field name
	 *
	 * @return ?string
	 *
	 * @throws \Exception Unsupported Letter Case setting
	 */
	public function normalizeName(string $name): ?string {
		$name = trim($name);
		$name = match ($this->enforce_letter_case) {
			static::LETTER_CASE_UPPER => strtoupper($name),
			static::LETTER_CASE_LOWER => strtolower($name),
			static::LETTER_CASE_NONE => $name,
			default => throw new Exception(
				"Letter Case \"{$this->enforce_letter_case}\" is not supported"
			)
		};

		$name = preg_replace('/[\-\s]/', '_', $name);
		$name = preg_replace('/[^\w\d_]+/', '', $name);

		if ($this->collapse_excessive_underscores) {
			$name = preg_replace('/_+/', '_', $name);
		}

		if (is_numeric($name[0])) {
			$name = "_{$name}";
		}

		return $name;
	}

	public function normalizeValue($value) {
		$q = $this->always_wrap_values;
		if (!is_string($value)) {
			$value = "{$value}";
		}
		$length = strlen($value);
		$last = $length - 1;
		if (
			($value[0] == '"' && $value[$last] == '"') ||
			($value[0] == "'" && $value[$last] == "'")
		) {
			// NOTE If the value is already wrapped, no auto-wrapping should happen!
			$q = null;
		}


		if (!empty($q)) {
			$value = "{$q}{$value}{$q}";
		}

		return $value;
	}
}
