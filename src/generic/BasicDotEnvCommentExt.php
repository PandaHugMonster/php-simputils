<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\Property;

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
 *
 * @property-read ?string $value Only for "PREFIX_ROW", in case if the comment-extension was used
 *                               as a wrapper for the value.
 */
abstract class BasicDotEnvCommentExt extends SimpleObject {

	// IMP  Do not redefine them! It will be marked as final at php 8.1 version
	const PREFIX_GLOBAL = '#:';
	const PREFIX_ROW = '#:#';

	protected mixed $_value = null;

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

		return "{$this->getPrefix()}\t{$name}{$params_str}";
	}

	/**
	 * Gets wrapped value
	 *
	 * Applicable only for "PREFIX_ROW"
	 *
	 * The value should be defined by the target class
	 *
	 * @return mixed|null
	 */
	#[Property('value')]
	public function getValue(): ?string {
		return $this->getPrefix() === static::PREFIX_ROW
			?$this->_value
			:null;
	}

	/**
	 * Wrap the value directly in place
	 *
	 * @param mixed $value     Value to wrap
	 * @param mixed ...$params Params for the constructor
	 *
	 * @return self
	 */
	public static function wrap(mixed $value, mixed ...$params): static {
		$obj = new static(...$params);
		$obj->_value = $value;
		return $obj;
	}

	public function __toString(): string {
		return $this->represent();
	}
}
