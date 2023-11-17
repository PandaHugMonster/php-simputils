<?php

namespace spaf\simputils\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\ReadOnlyProblem;
use spaf\simputils\PHP;

/**
 * Hierarchical Conf Model
 *
 * Generic H10l Conf Model that can be used for any purpose.
 *
 * @property-read string $type
 * @property-read Box $applied_confs
 *
 */
class H10lConf extends BoxRO {

	#[Property(type: Property::TYPE_GET)]
	protected ?string $_type = null;

	#[Property(type: Property::TYPE_GET)]
	protected ?Box $_applied_confs = null;

	/**
	 * @param H10lConf|Box|array $values Another config or an array with key-val pairs.
	 * @param null|string        $type   Represents a custom type of an object, if unfilled
	 *                                   short-name of a class is used.
	 */
	function __construct(H10lConf|Box|array $values, ?string $type = null) {

		parent::__construct();

		$this->_type = $type;
		if (empty($this->_type)) {
			$this->_type = $this::classShort();
		}
		$this->_applied_confs = PHP::box();
		$this->confApply($values);
	}

	/**
	 * @throws ReadOnlyProblem
	 */
	public function offsetUnset(mixed $offset): void {
		$this->cannotUseIt();
	}

//	//////////////////////

	function confApply(H10lConf|Box|array $conf): void {
		$type = "array";
		if ($conf instanceof H10lConf) {
			$type = $conf->type;
		}

		foreach ($conf as $key => $val) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$this->set($key, $val, true);
		}

		$this->_applied_confs[] = PHP::box([
			"type" => $type,
			"ref" => $conf,
		]);
	}

	static function redefComponentName(): string {
		return InitConfig::REDEF_H10L_CONF; // @codeCoverageIgnore
	}

}
