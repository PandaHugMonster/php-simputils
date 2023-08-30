#!/bin/env php
<?php
namespace spaf\simputils\bin;

use ReflectionClass;
use ReflectionMethod;
use spaf\simputils\attributes\markers\Deprecated;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\PHP;
use spaf\simputils\Str;
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
			static::_processEntity($class_ref);

			foreach ($class_ref->getMethods() as $method_ref) {
				static::_processEntity($method_ref, 8, 2);
			}
		}


	}

	protected static function _processEntity($entity_ref, int $padding = 2, int $new_lines = 1) {
		/** @var ReflectionClass $entity_ref */
		$attributes = $entity_ref->getAttributes(Deprecated::class);
		$padding = Str::mul(' ', $padding);
		$new_lines = Str::mul("\n", $new_lines);
		if (!empty($attributes)) {
			$attr_ref = $attributes[0];
			/** @var Deprecated $attr */
			$attr = $attr_ref->newInstance();

			$violation = '';
			if ($attr->since->major >= $attr->removed->major) {
				if ($violation) {
					$violation .= "\n";
				}
				$violation .= "\n\033[91m!!\033[0m{$padding}[Deprecation logic \033[96mVIOLATION\033[0m].\n" .
					"\033[91m!!\033[0m{$padding}The \"\033[94msince\033[0m\" version is higher or equal to the \"\033[95mremoved\033[0m\" version.";
			}
			if ($attr->removed->minor || $attr->removed->patch) {
				if ($violation) {
					$violation .= "\n";
				}
				$violation .= "\n\033[91m!!\033[0m{$padding}[Semantic Versioning \033[96mVIOLATION\033[0m].\n" .
					"\033[91m!!\033[0m{$padding}Removing of the deprecated items can be done only during the \"MAJOR\" release.\n" .
					"\033[91m!!\033[0m{$padding}\"minor\" and \"patch\" versions must be \"0\".\n" .
					"\033[91m!!\033[0m{$padding}Only \033[36mbackward-compatible\033[0m changes should be done in \"minor\" or \"patch\" versions.\n" .
					"\033[91m!!\033[0m{$padding}https://semver.org/#summary";
			}

			$type = match(true) {
				$entity_ref instanceof ReflectionClass => 'class',
				$entity_ref instanceof ReflectionMethod => 'method',
			};
			$status = "\033[91mDeprecated\033[0m";
			pr("--{$padding}{$status} {$type} \"\033[4m{$entity_ref->getName()}\033[0m\" is found");
			if ($attr->since) {
				pr("--{$padding}Deprecated since:    \033[94m{$attr->since}\033[0m");
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
				pr("--{$padding}Will be removed at:  \033[95m{$attr->removed}\033[0m {$addition}");
			}
			if ($violation) {
				pr("{$violation}\n");
			}
			pr("--{$padding}".Str::mul('-', 40));
			pr("--{$padding}Reason: \033[33m\"{$attr->reason}\"\033[0m");

			if ($attr->replacement) {
				pr("--{$padding}Replacement: \033[32m\"{$attr->replacement}\"\033[0m");
			}

			echo "{$new_lines}";

		}
	}

}

