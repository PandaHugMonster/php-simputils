#!/bin/env php
<?php
namespace spaf\simputils\bin;

use ReflectionClass;
use spaf\simputils\attributes\markers\Deprecated;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\pr;

include $_composer_autoload_path ?? __DIR__ . '/../../vendor/autoload.php';

class BinCodeAnalysis extends SimpleObject {

	static function dynamicDeprecationAnalysis() {
		PHP::init();

		$res = bx(get_declared_classes());

//		pr($res, $res->size);

		$php_version = PHP::version();
		$su_version = PHP::simpUtilsVersion();
		pr(
			"== PHP Version: \t\t{$php_version}",
			"== SimpUtils Version: \t{$su_version}\n",
		);

		foreach ($res as $class) {
			$class_ref = new ReflectionClass($class);
			$attributes = $class_ref->getAttributes(Deprecated::class);
			if (!empty($attributes)) {
				$attr_ref = $attributes[0];
				/** @var Deprecated $attr */
				$attr = $attr_ref->newInstance();

				$violation = '';
				if ($attr->since->major >= $attr->removed->major) {
					if ($violation) {
						$violation .= "\n";
					}
					$violation .= "\n\033[91m!!\033[0m  [Deprecation logic \033[96mVIOLATION\033[0m].\n" .
						"\033[91m!!\033[0m  The \"\033[94msince\033[0m\" version is higher or equal to the \"\033[95mremoved\033[0m\" version.";
				}
				if ($attr->removed->minor || $attr->removed->patch) {
					if ($violation) {
						$violation .= "\n";
					}
					$violation .= "\n\033[91m!!\033[0m  [Semantic Versioning \033[96mVIOLATION\033[0m].\n" .
						"\033[91m!!\033[0m  Removing of the deprecated items can be done only during the \"MAJOR\" release.\n" .
						"\033[91m!!\033[0m  \"minor\" and \"patch\" versions must be \"0\".\n" .
						"\033[91m!!\033[0m  Only \033[36mbackward-compatible\033[0m changes should be done in \"minor\" or \"patch\" versions.\n" .
						"\033[91m!!\033[0m  https://semver.org/#summary";
				}

				$type = 'class';
				$status = "\033[91mDeprecated\033[0m";
				pr("--  {$status} {$type} \"\033[4m{$class}\033[0m\" is found");
				if ($attr->since) {
					pr("--  Deprecated since:    \033[94m{$attr->since}\033[0m");
				}
				if ($attr->removed) {
					$addition = '';

					if (!$violation) {
						$diff = $attr->removed->major - $attr->since->major;
						if ($diff > 1) {
							$addition .= " ({$diff} major releases left before removal)";
						} else {
							$addition .= " (In the next major release it will be removed!)";
						}

					}
					pr("--  Will be removed at:  \033[95m{$attr->removed}\033[0m {$addition}");
				}
				if ($violation) {
					pr($violation);
				}
				pr("------------------------");
				pr("--  Reason: \033[33m\"{$attr->reason}\"\033[0m");

				if ($attr->replacement) {
					pr("--  Replacement: \033[32m\"{$attr->replacement}\"\033[0m");
				}

				pr("\n");

			}
		}


	}

}

