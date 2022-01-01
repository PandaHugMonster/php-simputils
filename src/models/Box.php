<?php

namespace spaf\simputils\models;

use ArrayObject;
use Exception;
use spaf\simputils\attributes\markers\Affecting;
use spaf\simputils\attributes\Property;
use spaf\simputils\PHP;
use spaf\simputils\traits\MetaMagic;
use spaf\simputils\traits\SimpleObjectTrait;
use function array_keys;
use function array_values;
use function count;
use function is_array;
use function is_null;
use function is_object;

/**
 * The Array-alike Box
 *
 * This is the ArrayObject inherited class. Which let you to use your object as an array storage.
 *
 * **Important:** Due to general PHP architecture (at least for PHP 8.0 version) limitations -
 * ArrayObject works slightly slower than a normal array (memory consumption is neglectably
 * the same). **This applicable only unless you redefine array functionality methods**.
 * Even if you are going to override them with single `parent::` method call in, the fact
 * of redefinition will slow down the object as an array up to 4 - 6 times for writing/setting
 * (compared to a simple array).
 *
 * Testings were done on 1 000 000 elements, and all the estimations are really rough,
 * but demonstrative enough.
 *
 * **DO NOT USE ArrayObject AND IT'S DERIVATIVES INSTEAD OF A NORMAL ARRAY**
 *
 * **IF YOU ARE GOING TO OVERRIDE ARRAY FUNCTIONALITY METHODS - YOU WILL COMPROMISE SPEED
 * OF EXECUTION**
 *
 * What is the purpose of this functionality:
 *  1.  For systems/extensions/libraries/frameworks developers to return objects array-alike.
 *      Good example of it is "read-only" and "array-alike" `PhpInfo` object. Benefit is that it's
 *      a single array-alike object, with "read-only" protection, that almost do not compromise
 *      the usage as array. For such cases, this tool is perfectly fitting.
 *  2.  Normal php arrays do not support easy integration into a string, while derivatives from this
 *      class will be using {@see Box::__toString()} to inline the array
 *      as a "Json String". So something like this is possible with such objects but not simple
 *      arrays:
 *      ```php
 *
 *          use spaf\simputils\PHP;
 *
 *          $obj_array = PHP::info();
 *          echo "My new shiny obj... I've meant array: {$obj_array}";
 *          // Normal arrays can't be used in such manner.
 *
 *      ```
 *  3.  A small but partially useful and comfortable thing: You can use intuitively the related
 *      functionality like `\count()` or `\array_keys()` or `\array_values()` with a string inline
 *      syntax.
 *      Example:
 *      ```php
 *          // Simple array and related to it info can't be easily embedded into the string
 *          $my_simple_array = ['te', 'st', 100_500];
 *
 *          // Just to use the related info about your array, you have to perform concatenation of
 *          // strings, which is as ugly as more difficult to analyse for the developer.
 *          echo "Here is the size ".count($my_simple_array)." of my very simple array.\n";
 *
 *          // You could try to create a temporary variable (which is not sugar, when you
 *          // have a lot of strings and inline usages)
 *          $tmp_var = count($my_simple_array);
 *          echo "Here is the size {$tmp_var} of my very simple array.\n";
 *
 *
 *          // And a good example how more comfortable and intuitive usage of Box is
 *          $my_special_array = box(['te', 'st', 100_500]);
 *          // or
 *          $my_special_array = new Box(['te', 'st', 100_500]);
 *          // Completely clear and intuitive way without temp variables and overwhelming syntax
 *          // layering
 *          echo "Here is the size {$my_special_array->size} of my very simple array.\n";
 *      ```
 *
 * In the most cases you would want to put into one line multiple info-pieces about your array.
 * So for example you want to log your array content, and the amount of items + list of keys used
 * in the array. Yes, a bit redundant, but it can be useful sometimes.
 *
 * Example:
 * ```php
 *      use function spaf\simputils\basic\box;
 *      $my_array = box(['val1', 'val2', 3]);
 *      for ($i = 0; $i < rand(5, 15); $i++) {
 *          if (($k = rand(0, 20)) > 10) {
 *              $my_array[] = "new: {$k}";
 *          }
 *      }
 *      echo "My array has {$my_array->size} items in it and the keys are: $my_array->keys.\n";
 *      echo "And values are {$my_array->values} \n";
 *      echo "The array itself is {$my_array} \n";
 * ```
 * Would output something like that:
 * ```
 * My array has 6 items in it and the keys are: [0,1,2,3,4,5].
 * And values are ["val1","val2",3,"new: 14","new: 11","new: 18"]
 * The array itself is ["val1","val2",3,"new: 14","new: 11","new: 18"]
 * ```
 *
 * **Important:** The setting {@see \spaf\simputils\PHP::$use_box_instead_of_array} does not
 * directly affect this class. So when you disable it, it will disable those everywhere but here.
 * This is done on purpose, because gives a developer really good level of flexibility. If you want
 * enforce array returning instead of Box from this class (which is recommended against of that),
 * just redefine this class, and register your new child class instead of Box class.
 *
 * In the most cases it's intuitive enough to use {@see \spaf\simputils\basic\box()} syntax instead
 * of `new Box()`. Because function is really short when imported/used. Though both notations are
 * identical by functionality. `box()` is just a shortcut for {@see PHP::box()}, and `PHP::box()`
 * is a shortcut for `new Box()`.
 *
 * TODO Implement more of array-related functionality from default PHP pool
 *
 * FIX  Implement switchable on/off for silent "null" instead of exception return
 *
 * @package spaf\simputils\generic
 *
 * @property-read mixed $stash Stash suppose to contain additional data that was prepared during
 *                             some of functionality like {@see Box::shift()}
 * @property-read int $size
 * @property-read Box|array $keys
 * @property-read Box|array $values
 */
class Box extends ArrayObject {
	use SimpleObjectTrait;
	use MetaMagic;

	public static bool $to_string_format_json = true;
	public static bool $is_json_pretty = false;

	protected mixed $_stash = null;

	/**
	 * @return mixed
	 */
	#[Property('stash')]
	protected function getStashContent(): mixed {
		return $this->_stash;
	}

	/**
	 * @return int
	 */
	#[Property('size')]
	protected function getSize() {
		return count($this);
	}

	/**
	 * @return static|Box|array
	 */
	#[Property('keys')]
	protected function getKeys(): static|Box|array {
		return new static(array_keys((array) $this));
	}

	/**
	 * @return static|Box|array
	 */
	#[Property('values')]
	protected function getValues(): static|Box|array {
		return new static(array_values((array) $this));
	}

	/**
	 * Slices out the portion of array
	 *
	 *  1.  If first value is int, then from it's position till the end of th array
	 *  2.  If first value is array - then it means "extract values by keys", so this array will be
	 *      considered as keys, values of which should be sliced out.
	 *      `$stop` is being ignored in this case.
	 *
	 * **Important:** If array is supplied, the order of the result array will depend on the order
	 * of the supplied array.
	 *
	 * @param int|array $from Array with name of keys to slice out, or the starting index
	 * @param int|null  $to   The ending index, ignored if the array provided to the first argument
	 *
	 * @return Box|array
	 * @throws \Exception Exception if the `$from` number is bigger than `$to` number
	 */
	public function slice(int|array $from = 0, ?int $to = null): Box|array {
		$size = $this->size;
		$res = new static();

		if (is_array($from)) {
			foreach ($from as $key) {
				if (isset($this[$key])) {
					$res[$key] = is_object($this[$key])
						?clone $this[$key]
						:$this[$key];
				}
			}
		} else {
			$i = 0;

			if (is_null($to)) {
				$to = $size;
			}
			if ($to < 0) {
				$to = $size + $to; // considered "- $to"
			}
			if ($from < 0) {
				$from = $size + $from; // considered "- $from"
			}
			if ($from > $to) {
				throw new Exception('$from value cannot be bigger than $to value');
			}

			foreach ($this->keys as $key) {
				if ($i >= $from && $i < $to) {
					$res[] = is_object($this[$key])
						?clone $this[$key]
						:$this[$key];
				}
				$i++;
			}
		}

		return $res;
	}

	/**
	 * Shift array items (analogue of php function `\array_shift()`)
	 *
	 * **This method affects this object!**
	 *
	 * It returns the same object-box with removed specified amount of elements from the specified
	 * side (by default 1 item from the left/start side)
	 *
	 * All the removed elements are put into `stash` read-only field. All previous values will be
	 * wiped out. It's not incremental.
	 *
	 * **Important:** Even though the `\array_shift()` was referenced as an example of similar
	 * functionality. Code inside might not use it from purpose of efficiency, despite the fact
	 * of the same functionality!
	 *
	 * @see \array_shift()
	 *
	 * @param int  $amount     Amount of elements to shift
	 * @param bool $from_start If set to true, then the shift will be done on the left side (start
	 *                         side), otherwise it's don on the right side (end side). Default true.
	 *
	 * @return static
	 */
	#[Affecting]
	public function shift(int $amount = 1, bool $from_start = true): static {
		$temp_stash = new static();
		$box = $from_start
			?$this
			:$this->reversed();
		$i = 0;
		foreach ($box as $k => $v) {
			if ($i++ >= $amount) {
				break;
			}

			$temp_stash[$k] = $v;
		}

		foreach ($temp_stash as $key => $val) {
			unset($this[$key]);
		}

		// NOTE Important to store the resulting removed elements in the stash, if they would be
		//      needed for the user.
		$this->_stash = $temp_stash;

		return $this;
	}

	/**
	 * Returns a new Box with reversed position of data
	 *
	 * @return Box|array
	 */
	public function reversed(): Box|array {
		return new static(array_reverse((array) $this));
	}

	/**
	 * To a normal PHP array
	 *
	 * @inheritdoc
	 *
	 * @param bool $with_class Pack with class, default is "false"
	 *
	 * @codeCoverageIgnore
	 * @return array
	 */
	public function toArray(bool $with_class = false): array {
		$res = (array) $this;
		if ($with_class)
			$res[PHP::$serialized_class_key_name] = static::class;
		return $res;
	}

	/**
	 * @param array $data Data array
	 *
	 * @codeCoverageIgnore
	 * @return $this
	 */
	public function ___setup(array $data): static {
		foreach ($data as $key => $val) {
			$this[$key] = $val;
		}
		return $this;
	}

	/**
	 * To debug as a normal array
	 *
	 * @codeCoverageIgnore
	 * @return array|null
	 */
	public function __debugInfo(): ?array {
		return $this->toArray();
	}
}
