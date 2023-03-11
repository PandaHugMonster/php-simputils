<?php

namespace spaf\simputils\exceptions\types;

use Exception;

class NonIntObjException extends Exception {

	protected $message = 'This object does not support int conversion. Override ___int() method';

}
