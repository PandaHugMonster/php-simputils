<?php

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use function spaf\simputils\basic\pd;

require_once 'vendor/autoload.php';

//$phpi = PHP::info();
//pd($phpi);

trait mynewy {

	#[Property]
	public function ttt() {
		return 'WUT!?';
	}
}

class GGG extends SimpleObject {
	#[Property]
	public function ttt() {
		return 'WUT!?';
	}
}

/**
 * @property-read $ttt
 */
class A extends GGG {

//	#[Property]
//	public function ttt() {
//		return 'coocoo';
//	}
}

$a1 = new A();

pd($a1->ttt);
