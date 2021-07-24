<?php


namespace spaf\simputils;


use Closure;
use ValueError;

/**
 * Simputils Settings class
 *
 * @package spaf\simputils
 */
class Settings {

	const REDEFINED_PD = 'pd';
	const SO_SNAKE_CASE = 'snake_case';
	const SO_CAMEL_CASE = 'camelCase';

	private static array $_is_redefined_map = [];
	private static ?Closure $_redefined_pd = null;
	private static string $_redefined_simple_object_type_case = self::SO_SNAKE_CASE;

	/**
	 * Redefine Please Die functionality
	 *
	 * Provide a callback that should be run instead of the default pd() functionality
	 * To clear it out to default/initial functionality, provide null instead of callback.
	 *
	 * @see \spaf\simputils\pd()
	 *
	 * @param null|Closure $callback
	 */
	public static function redefine_pd(?Closure $callback): void {
		static::$_is_redefined_map[static::REDEFINED_PD] = !is_null($callback);
		static::$_redefined_pd = $callback;
	}

	/**
	 * Checks if a component was redefined
	 *
	 * @param string $component_key
	 *
	 * @return bool
	 */
	public static function is_redefined(string $component_key): bool {
		return
			!empty(static::$_is_redefined_map[$component_key])
			&& static::$_is_redefined_map[$component_key];
	}

	/**
	 * Returns redefined component callback by constant/key name
	 *
	 * @param string $component_key
	 *
	 * @return Closure|null
	 */
	public static function get_redefined(string $component_key): ?Closure {
		$property_name = '_redefined_'.$component_key;
		if (empty(static::$$property_name))
			return null;
		return static::$$property_name;
	}

	public static function get_simple_object_type_case(): string {
		return static::$_redefined_simple_object_type_case;
	}

	public static function set_simple_object_type_case(string $val): void {
		if (in_array($val, [static::SO_SNAKE_CASE, static::SO_CAMEL_CASE]))
			static::$_redefined_simple_object_type_case = $val;
		else {
			throw new ValueError('Simple Object type case can be only: '.static::SO_SNAKE_CASE.' / '.static::SO_CAMEL_CASE);
		}
	}

	public static function version(): string {
		return '0.2.2';
	}

	public static function license(): string {
		return 'MIT';
	}
}