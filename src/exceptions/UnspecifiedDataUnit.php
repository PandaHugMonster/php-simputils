<?php


namespace spaf\simputils\exceptions;


use Exception;

/**
 * Class UnspecifiedDataUnit
 * Exception class that notifies about the unit abbreviation is not specified
 *
 * Basically it means that you have forgotten to provide the unit abbreviation.
 *
 * @package spaf\simputils\exceptions
 */
class UnspecifiedDataUnit extends Exception {

	protected $message = 'Data unit is not specified';

}