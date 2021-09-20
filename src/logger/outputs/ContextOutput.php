<?php


namespace spaf\simputils\logger\outputs;


class ContextOutput extends BasicOutput {

	public function log($msg) {
		echo "$msg\n";
	}
}