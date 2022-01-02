<?php


namespace spaf\simputils\logger\outputs;


class ContextOutput extends BasicOutput {

	public function log($msg, $priority = null) {
		echo "$msg\n";
	}
}