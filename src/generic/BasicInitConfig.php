<?php

namespace spaf\simputils\generic;

use Closure;
use spaf\simputils\attributes\Property;
use spaf\simputils\models\Dir;
use function register_shutdown_function;

/**
 *
 * @property ?Dir $code_root
 * @property ?Dir $working_dir
 */
abstract class BasicInitConfig extends SimpleObject {

	#[Property]
	protected ?Dir $_code_root = null;

	#[Property]
	protected ?Dir $_working_dir = null;

	function run(...$params) {
		$this->_init(...$params);
	}

	private function _init(...$params) {
		register_shutdown_function(Closure::fromCallable([$this, '_exit']));
		$this->preInit(...$params);
		$this->init(...$params);
		$this->postInit(...$params);
	}

	function preInit(...$params) {

	}

	function init(...$params) {

	}

	function postInit(...$params) {

	}

	private function _exit() {
		$this->preExit();
		$this->exit();
		$this->postExit();
	}

	function preExit() {

	}

	function exit() {

	}

	function postExit() {

	}

}
