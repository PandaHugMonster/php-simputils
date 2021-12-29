<?php

namespace spaf\simputils\generic;

/**
 * Basic DotEnv Comment Extension class
 *
 * NOTE Format is simple, and the mostly defined by each extension itself, but common rule
 *      is following:
 *       1. Comment line must start with the prefix (that includes first # symbol
 *          meaning comment)
 *       2. Then goes space (at least 1)
 *       3. Then goes the name of extension (it would not contain spaces, those are trimmed even
 *          if used, so please do not use spaces in comment-extension name)
 *       4. The last thing for common format is space (at least 1)
 *       5. Anything that goes after - is defined by the extensions!
 */
abstract class BasicDotEnvCommentExt extends SimpleObject {

	// IMP  Do not redefine them! It will be marked as final at php 8.1 version
	const PREFIX_GLOBAL = '#:';
	const PREFIX_ROW = '#:#';

	/**
	 * Returns unique name of this comment-extension
	 *
	 * IMP  Must not contain spaces, those will be trimmed during processing!
	 *
	 * @return string
	 */
	abstract public static function getName(): string;

	/**
	 * Prefix type
	 *
	 * @return string
	 */
	abstract public function getPrefix(): string;

	/**
	 * Params should return generated string of params
	 *
	 * @return string|null
	 */
	abstract public function params(): ?string;

	/**
	 * Parsing or returning false if not fitting
	 *
	 * @param string $value Comment-extension string
	 *
	 * @return false|static
	 */
	abstract public static function parse(string $value): static|false;

	/**
	 * Representation of the object (basically just a string representation in place)
	 *
	 * @return string|null
	 */
	public function represent(): ?string {
		$name = str_replace(' ', '', static::getName());

		$params_str = $this->params();
		if (!empty($params_str)) {
			$params_str = " {$params_str}";
		}

		return "{$this->getPrefix()} {$name}{$params_str}";
	}

	public function __toString(): string {
		return $this->represent();
	}
}
