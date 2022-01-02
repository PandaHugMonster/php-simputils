<?php

namespace spaf\simputils\special\dotenv;

use spaf\simputils\generic\BasicDotEnvCommentExt;

/**
 *
 * Example:
 * ```
 *      #: include /tmp/my-path-omg.txt
 * ```
 *
 * FIX  Unfinished. Proceed with comment-extensions after the current pull-request is resolved.
 * FIX  Maybe define the full format for the extension params, will be better!
 */
class ExtInclude extends BasicDotEnvCommentExt {

	public function getPrefix(): string {
		return static::PREFIX_GLOBAL;
	}

	public static function getName(): string {
		return 'include';
	}

	public function __construct(
		public string $file
	) {}

	public function params(): ?string {
		return $this->file;
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
