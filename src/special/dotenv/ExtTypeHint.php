<?php

namespace spaf\simputils\special\dotenv;

use spaf\simputils\generic\BasicDotEnvCommentExt;
use spaf\simputils\PHP;

/**
 *
 * Example:
 * ```
 *      #:# type-hint int
 *      PARAM_1="1234"
 * ```
 *
 * FIX  Unfinished. Proceed with comment-extensions after the current pull-request is resolved.
 * FIX  Maybe define the full format for the extension params, will be better!
 *
 * IMP  **Important:** For the security reasons type casting for non-standard primitives like custom
 *      classes and etc. can be performed only when it's registered.
 *      Uncontrolled "code/scripts execution" from dotenv and comments - strictly prohibited!
 *      If you remove this lock in your sub-libraries, your library must be considered UNSAFE!
 *      And you need to make a notable statement that you changed those mechanisms.
 * @codeCoverageIgnore
 */
class ExtTypeHint extends BasicDotEnvCommentExt {

	public function getPrefix(): string {
		return static::PREFIX_ROW;
	}

	public static function getName(): string {
		return 'type-hint';
	}

	public function __construct(
		public ?string $type = null
	) {}

	public function params(): ?string {
		return $this->type ?? PHP::type($this->_value);
	}

	/**
	 * Parser
	 *
	 * If successfully parsed returns fulfilled object, otherwise false.
	 *
	 * **Important:** If the content seems unfitting (for example the structure is for another
	 * extension) - then must be returned false, so the next registered dotenv comment extension
	 * would be able to try out on this line.
	 *
	 * @param string $value Parsable dotenv comment-extension string
	 *
	 * @return false|static Returns object of the same time if parsed successfully, or false
	 *                      if the string is not fitting to this class
	 */
	public static function parse(string $value): static|false {
		$res = false;

		return $res;
	}
}
