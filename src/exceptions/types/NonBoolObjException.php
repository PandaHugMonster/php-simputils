<?php

namespace spaf\simputils\exceptions\types;

use Exception;

class NonBoolObjException extends Exception {

	protected $message = 'This object does not support bool conversion. Override ___bool() method';

}
