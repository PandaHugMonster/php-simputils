<?php

namespace spaf\simputils\basic;

use spaf\simputils\Settings;

/**
 * Please Die function
 *
 * Print out all the supplied params, and then die/exit the runtime.
 * Basically, could be considered as a shortcut of sequence of "print_r + die"
 *
 * Besides that, the functionality can be redefined. For example if you want
 * use your own implementation, you can just redefine it on a very early runtime stage
 * with the following code:
 *      use spaf\simputils\Settings;
 *      Settings::redefine_pd([$your_obj, $method_name]);
 *
 * @param ...$args
 *
 * @see \die()
 *
 * @see \print_r()
 */
function pd(...$args) {
	if (Settings::is_redefined(Settings::REDEFINED_PD)) {
		$callback = Settings::get_redefined(Settings::REDEFINED_PD);
		$callback(...$args);
	} else {
		foreach ($args as $arg) {
			print_r($arg);
			echo "\n";
		}
		die();
	}
}