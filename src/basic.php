<?php

/**
 * Procedural shortcuts for functionality of `spaf\simputils\PHP`
 */
namespace spaf\simputils\basic;
use spaf\simputils\PHP;

/**
 * Please Die function
 *
 * Print out all the supplied params, and then die/exit the runtime.
 * Basically, could be considered as a shortcut of sequence of "print_r + die"
 *
 * Besides that, the functionality can be redefined. For example if you want
 * use your own implementation, you can just redefine it on a very early runtime stage
 * with the following code:
 * ```php
 *      use spaf\simputils\Settings;
 *      Settings::redefine_pd($your_obj->$method_name(...));
 *      // or using anonymous functions
 *      Settings::redefine_pd(
 *          function (...$args) {
 *              echo "MY CALLBACK IS BEING USED\n";
 *              print_r($args);
 *              die;
 *          }
 *      );
 * ```
 *
 * @param mixed ...$args Anything you want to print out before dying
 *
 * @see PHP::pd()
 * @see \die()
 * @see \print_r()
 */
function pd(...$args) {
	PHP::pd(...$args);
}
