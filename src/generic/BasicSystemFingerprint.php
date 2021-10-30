<?php

namespace spaf\simputils\generic;

use Exception;
use spaf\simputils\attributes\Property;
use spaf\simputils\generic\constants\ConstSystemFingerprint as constants;
use spaf\simputils\logger\Logger;
use spaf\simputils\PHP;
use function array_shift;
use function array_slice;
use function count;
use function current;
use function explode;
use function is_integer;
use function is_null;
use function is_string;
use function key;
use function preg_replace;
use function reset;

/**
 *
 * @property-read array $parts
 * @property-read string $name
 */
abstract class BasicSystemFingerprint extends SimpleObject {

	protected static string $algorithm_one = constants::ALGO_MD5;
	protected static string $algorithm_two = constants::ALGO_SHA256;

	public string $first_hash;
	public string $second_hash;

	protected bool $is_no_data = false;

	/**
	 * Getting parts
	 *
	 * Must return array of only values-strings (integer indexes), that representing
	 * fields/properties.
	 *
	 * **Order of the parts - is crucially essential!**
	 *
	 * @return array
	 */
	#[Property('parts')]
	abstract public function getParts(): array;

	/**
	 * The name of the SystemFingerprint class
	 *
	 * @return string
	 */
	#[Property('name')]
	abstract public function getName(): string;

	/**
	 * @return mixed
	 */
	#[Property('data')]
	abstract public function getData(): mixed;

	/**
	 * Constructor
	 *
	 * The major functionality has 2 ways:
	 *  *   Parsing string
	 *  *   Creating new object from params
	 *
	 * To parse, you have to create a new object with key params "parse" or first param as string.
	 *
	 * To create a new object from params - provide params and "parse" => false, or do not provide
	 * first param as a string (Because first string param would mean parsing)
	 *
	 * Ideally always provide keys for your params here (the safest way to use the object)
	 *
	 * If you would want to redefine constructor, don't forget to call `parent::__construct()`
	 * after your constructor code. In the most cases it's better to redefine use `static::init()`
	 * method.
	 *
	 * @param mixed ...$params Parameters for the constructor
	 *
	 * @throws \ReflectionException Reflection Exception
	 */
	public function __construct(mixed ...$params) {

		$parsable = null;

		if (count($this->parts) !== count($params))

		reset($params);
		if (isset($params['parse'])) {
			$parsable = $params['parse'];
		}
		if (empty($parsable) && is_integer(key($params))) {
			$first = current($params);
			if (is_string($first)) {
				$parsable = $first;
			}
		}

		if (!empty($parsable)) {
			$parsed_params = static::parse($parsable);
			$this->assignParams($parsed_params);
		} else {
			$this->assignParams($params);
			$this->fulfillFromData();
		}

		$this->init();
	}

	/**
	 * Fields assigning from params
	 *
	 * If the array is integer indexed - then order of `$this->parts` is used, otherwise
	 * the values assigned directly by keys/names of properties.
	 *
	 * @param array $params Params to assign to fields
	 *
	 * @return void
	 * @throws \Exception Exception if indices' types are not homogenous
	 */
	protected function assignParams(array $params) {
		$index_type = null;
		// TODO Move to PHP as "areKeysHomogenous" and "areKeysHeterogenous"
		foreach ($params as $k => $v) {
			$t = PHP::type($k);
			if (is_null($index_type)) {
				$index_type = $t;
			} else if ($index_type !== $t) {
				// FIX  Should be implemented differently (to use both int and str keys)
				throw new Exception( // @codeCoverageIgnore
					'Index/keys must be of the same type' // @codeCoverageIgnore
				);
			}
		}

		foreach ($this->parts as $i => $field) {
			if (!isset($params[$i]) && !isset($params[$field])) {
				continue;
			}

			$val = $index_type === 'integer'
				?$params[$i]
				:$params[$field];

			$this->$field = $this->preCheckProperty($field, $val);
		}
	}

	/**
	 * @param string $field Property/field na,e
	 * @param mixed  $val   Value
	 *
	 * @return mixed
	 */
	protected function preCheckProperty(string $field, mixed $val): mixed {
		return $val;
	}

	/**
	 * Getting data and generating hashes
	 *
	 * @return void
	 */
	protected function fulfillFromData() {
		$data = $this->getData();

		$this->first_hash = static::processThroughAlgorithm($data, static::$algorithm_one);
		$this->second_hash = static::processThroughAlgorithm($data, static::$algorithm_two);
	}

	/**
	 * @return string
	 */
	protected function generateString(): string {
		$res = $this->name.'/'.$this->first_hash.','.$this->second_hash;

		if (!empty($this->parts)) {
			foreach ($this->parts as $part) {
				if (!isset($this->$part)) {
					throw new Exception( // @codeCoverageIgnore
						"\"{$part}\" property is not specified" // @codeCoverageIgnore
					);
				}
				$res .= '/'.$this->$part;
			}
		}
		return $res;
	}

	/**
	 * @param mixed  $data Data
	 * @param string $algo Algorithm name
	 *
	 * @return string
	 */
	public static function processThroughAlgorithm(mixed $data, string $algo): string {
		$ser_str = null;
		try {
			$ser_str = PHP::serialize($data, PHP::SERIALIZATION_TYPE_JSON);
		} catch (Exception $e) { // @codeCoverageIgnore
			Logger::error($e->getMessage()); // @codeCoverageIgnore
		}
		return hash($algo, $ser_str);
	}

	/**
	 * @param string $string String to parse
	 *
	 * FIX  Refactor the params acquiring
	 * @return void|array
	 * @throws \ReflectionException Reflection error
	 */
	protected static function parse(string $string): ?array {
		if (is_null($string)) {
			return null; // @codeCoverageIgnore
		}

		// Cleaning duplicated slashes
		$string = preg_replace('#/+#', '/', $string);

		$parts = explode('/', $string);

		if (!empty($parts) && empty($parts[0])) {
			array_shift($parts); // @codeCoverageIgnore
		}

		if (empty($parts) || empty($parts[0]) || empty($parts[1])) {
			throw new Exception( // @codeCoverageIgnore
				'Parsing has failed, too few data-parts are found' // @codeCoverageIgnore
			);
		}

		$instance = static::createDummy();
		$instance->is_no_data = true;

		$name = $parts[0];
		if ($name !== $instance->getName()) {
			throw new Exception('Instance name does not match'); // @codeCoverageIgnore
		}

		[$instance->first_hash, $instance->second_hash] = explode(',', $parts[1]);

		// Excluding the common params, and leaving only custom params
		return array_slice($parts, 2);
	}

	/**
	 * @return void
	 */
	protected function init(): void {
		// empty body
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->generateString();
	}
}
