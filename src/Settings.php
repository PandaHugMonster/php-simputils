<?php


namespace spaf\simputils;


use Closure;
use spaf\simputils\models\Version;
use ValueError;

/**
 * Simputils Settings class
 *
 * @package spaf\simputils
 */
class Settings {

	/**
	 * Key for PleaseDie redefining
	 */
	const REDEFINED_PD = 'pd';
	/**
	 * Key for snake case
	 */
	const SO_SNAKE_CASE = 'snake_case';
	/**
	 * Key for camel case
	 */
	const SO_CAMEL_CASE = 'camelCase';

	/**
	 * @var string|null Runtime application name
	 */
	public static ?string $app_name = null;
	/**
	 * @var string|null Code root path
	 */
	public static ?string $code_root = null;

	/**
	 * @var bool Flag if the framework was already prepared
	 */
	private static bool $is_prepared = false;

	/**
	 * @var array Is redefined map
	 */
	private static array $_is_redefined_map = [];
	/**
	 * @var \Closure|null PleaseDie redefinition
	 */
	private static ?Closure $_redefined_pd = null;
	/**
	 * @var string Object type cases for SimpleObject
	 */
	private static string $_redefined_simple_object_type_case = self::SO_CAMEL_CASE;

	/**
	 * Redefine Please Die functionality
	 *
	 * Provide a callback that should be run instead of the default pd() functionality
	 * To clear it out to default/initial functionality, provide null instead of callback.
	 *
	 * @see \spaf\simputils\PHP::pd()
	 *
	 * @param null|Closure $callback Callback to use instead of pd() default one
	 */
	public static function redefinePd(?Closure $callback): void {
		static::$_is_redefined_map[static::REDEFINED_PD] = !is_null($callback);
		static::$_redefined_pd = $callback;
	}

	/**
	 * Checks if a component was redefined
	 *
	 * @param string $component_key Component key
	 *
	 * @return bool
	 */
	public static function isRedefined(string $component_key): bool {
		return
			!empty(static::$_is_redefined_map[$component_key])
			&& static::$_is_redefined_map[$component_key];
	}

	/**
	 * Returns redefined component callback by constant/key name
	 *
	 * @param string $component_key Component key
	 *
	 * @return Closure|null
	 */
	public static function getRedefined(string $component_key): ?Closure {
		$property_name = '_redefined_'.$component_key;
		if (empty(static::$$property_name))
			return null;
		return static::$$property_name;
	}

	/**
	 * Getting the type case of Simple Object (general)
	 *
	 * @return string
	 */
	public static function getSimpleObjectTypeCase(): string {
		return static::$_redefined_simple_object_type_case;
	}

	/**
	 * Setting the type case of Simple Object (general)
	 *
	 * @param string $val Type case
	 *
	 * @return void
	 */
	public static function setSimpleObjectTypeCase(string $val): void {
		if (in_array($val, [static::SO_SNAKE_CASE, static::SO_CAMEL_CASE]))
			static::$_redefined_simple_object_type_case = $val;
		else {
			throw new ValueError(
				'Simple Object type case can be only: '
				.static::SO_SNAKE_CASE
				.' / '
				.static::SO_CAMEL_CASE
			);
		}
	}

	/**
	 * Framework/lib version
	 *
	 * @return \spaf\simputils\models\Version|string
	 */
	public static function version(): Version|string {
		return new Version('0.2.3', 'SimpUtils');
	}

	/**
	 * Framework/lib license
	 *
	 * @return string
	 */
	public static function license(): string {
		return 'MIT';
	}

	/**
	 * Initial preparation of the framework
	 *
	 * Should be called as early as possible, to prepare framework. It's strongly advised to use
	 * this method in every application on the very early stage. Some functionality strongly
	 * relying on this preparations (for example DotEnv, etc.).
	 *
	 * @param string|null $app_name  Application name
	 * @param string|null $code_root Code root dir
	 * @param bool        $enforce   Enforce re-preparing the framework
	 *
	 * @return void
	 */
	public static function prepare(
		?string $app_name = null,
		?string $code_root = null,
		bool $enforce = false,
	) {
		if ($enforce || !static::$is_prepared) {
			static::$app_name = $app_name;
			static::$code_root = $code_root;
			static::$is_prepared = true;
		}
	}
}
