<?php

namespace spaf\simputils\exceptions\types;

use Exception;

class NonFloatObjException extends Exception {

	protected $message = 'This object does not support float conversion. Override ___float() method';

}
