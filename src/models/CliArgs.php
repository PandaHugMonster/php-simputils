<?php

namespace spaf\simputils\models;

use Exception;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;

/**
 *
 * WARN Experimental, do not consider as finished. Architecture might be completely volatile
 * @property-read Box|array $args Returns list of arguments WITHOUT executed filename
 * @property-read Box|array $executable Executable file name
 */
class CliArgs extends SimpleObject {

	protected bool $_with_executable = false;
	protected array $_orig_args = [];
	protected ?Box $_args;
	protected ?string $_executed_file_name;
	protected ?string $_executed_file_base_path;

	public function __construct($args, $with_executable = false) {
		if (empty($args)) {
			throw new Exception('Arguments are empty. Can not parse and prepare CliArgs object');
		}
		$this->_with_executable = $with_executable;
		$this->_orig_args = $args;
		$this->parse();
	}

	protected function parse() {
		$orig_args = $this->_orig_args;

		$first_item = null;
		if ($this->_with_executable) {
			$first_item = array_shift($orig_args);
		}
		$this->_executed_file_name = $first_item;
		$this->_args = new Box($orig_args);
	}

	#[Property('args')]
	protected function getArgs(): Box|array {
		return $this->_args;
	}

	#[Property('executable')]
	protected function getExecutable(): ?string {
		return $this->_executed_file_name;
	}
}
