<?php

namespace spaf\simputils\exceptions\types;

use Exception;

class NonArrayObjException extends Exception {

	protected $message = 'This object does not support array conversion. Override ___array() method';

}
