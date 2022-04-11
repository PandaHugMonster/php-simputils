<?php

namespace spaf\simputils\models;

use ArrayObject;
use Closure;
use Exception;
use Generator;
use spaf\simputils\attributes\Extract;
use spaf\simputils\attributes\markers\Affecting;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\Math;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use spaf\simputils\traits\MetaMagic;
use spaf\simputils\traits\RedefinableComponentTrait;
use spaf\simputils\traits\SimpleObjectTrait;
use function array_combine;
use function array_flip;
use function array_keys;
use function array_values;
use function arsort;
use function count;
use function implode;
use function in_array;
use function is_array;
use function is_float;
use function is_int;
use function is_null;
use function is_numeric;
use function is_object;
use function is_string;
use function shuffle;
use function uasort;

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
 * In the most cases it's intuitive enough to use {@see \spaf\simputils\basic\bx()} syntax instead
 * of `new Box()`. Because function is really short when imported/used. Though both notations are
 * identical by functionality. `box()` is just a shortcut for {@see PHP::box()}, and `PHP::box()`
 * is a shortcut for `new Box()`.
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
	use RedefinableComponentTrait;

	public static bool $to_string_format_json = true;
	public static bool $is_json_pretty = false;

	#[Extract(false)]
	protected mixed $_stash = null;

	/**
	 * @return mixed
	 */
	#[Extract(false)]
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
	#[Extract(false)]
	#[Property('keys')]
	protected function getKeys(): static|Box|array {
		return new static(array_keys((array) $this));
	}

	/**
	 * @return static|Box|array
	 */
	#[Extract(false)]
	#[Property('values')]
	protected function getValues(): static|Box|array {
		return new static(array_values((array) $this));
	}

	/**
	 * @return static|Box|array
	 */
	#[Extract(false)]
	public function flipped(): static|Box|array {
		// TODO Improve flipping so it would hash objects when possible for keys
		return new static(array_flip((array) $this));
	}

//	/**
//	 * Determines if the given array is a list
//	 *
//	 * An array is considered a list if its keys consist of consecutive
//	 * numbers from 0 to count($array)-1.
//	 *
//	 * @return bool
//	 */
//	#[Shortcut('\array_is_list()')]
//	#[Property('isList')]
//	protected function getIsList(): bool {
//		return array_is_list((array) $this);
//	}

	/**
	 * Returns key by the specified value
	 *
	 * @param mixed $value Value
	 *
	 * @return string|null
	 */
	public function getKeyByValue(mixed $value): ?string {
		return $this->flipped()[$value] ?? null;
	}

	/**
	 * Slices out the portion of array
	 *
	 *  1.  If first value is int, then from it's position till the end of th array
	 *  2.  If first value is array - then it means "extract values by keys", so this array will be
	 *      considered as keys, values of which should be sliced out.
	 *      `$to` is being ignored in this case.
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
					$res[$key] = is_object($this[$key])
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
	 * Clears the content and load from box/arrays the new one
	 *
	 * If multiple boxes/arrays supplied - then they are merged
	 *
	 * @return self
	 */
	public function load(Box|array ...$args) {
		// NOTE Clearing content of our Box
		$this->exchangeArray([]);
		return $this->mergeFrom(...$args);
	}

	/**
	 * Filtering elements
	 *
	 * TODO Add more intuitive filtering ways
	 *
	 * @param \Closure|callable|null $callback Callback that receives $val, [$key, $self]
	 *                                         arguments, and should return "true" if value
	 *                                         should be resent in the result, and false
	 *                                         otherwise
	 *
	 * @return static A new box instance with filtered elements
	 */
	public function filter(null|Closure|callable $callback = null): static {
		if (is_null($callback)) {
			return $this->clone();
		}

		$res = new static;
		foreach ($this as $key => $val) {
			if ($callback($val, $key, $this)) {
				$res[$key] = $val;
			}
		}
		return $res;
	}

	/**
	 * Iterates through elements with a callback
	 *
	 * Additional perk - you can filter elements if you return null instead of array of 2 elements!
	 *
	 * @param \Closure|callable|null $callback Callback that should return array of 2
	 *                                         elements [$key, $value] that should be
	 *                                         included into the result.
	 *                                         Returning empty value like null or empty array
	 *                                         will filter the whole element out
	 *
	 * @return static A new box instance with processed elements
	 */
	public function each(null|Closure|callable $callback = null): static {
		if (is_null($callback)) {
			return $this->clone();
		}
		$res = new static;
		foreach ($this as $key => $val) {
			$sub_res = $callback($key, $val, $this);
			if (!empty($sub_res)) {
				[$key, $val] = $sub_res;
				$res[$key] = $val;
			}
		}
		return $res;
	}

	/**
	 * Removing elements by key(s)
	 *
	 * @param int|string ...$keys Keys of items that should be remove/unset
	 *
	 * FIX  Add "unsetByValue" and "removeDuplicates"
	 * @return $this
	 */
	public function unsetByKey(int|string ...$keys): self {
		foreach ($keys as $key) {
			if (!empty($this[$key])) {
				unset($this[$key]);
			}
		}

		return $this;
	}

	/**
	 * Extract specified keys and their values
	 *
	 * Does not remove those keys and values from the current box
	 *
	 * @param int|string ...$keys Keys of items that should be extracted
	 *
	 * @return $this
	 */
	public function extract(int|string ...$keys): static {
		$res = new static();
		foreach ($keys as $key) {
			$res[$key] = $this[$key];
		}

		return $res;
	}

	/**
	 * Merge boxes/arrays arguments into current object
	 *
	 * All the numerical keys will not be overwritten.
	 * All the non-numerical keys will be replaced if already present
	 *
	 * **Important:** The arguments will not be modified! The values from them are copied!
	 *
	 * The merge is not being done through {@see \array_replace_recursive()} due to
	 * difference of logic.
	 *
	 * It's not recursive!
	 *
	 *
	 * FIX  Implement "mergeFromRecursive" and "mergeFromStrict" at some point
	 * @param self|array ...$boxes Boxes/Arrays that should be merged
	 *
	 * @return self Returns self reference
	 */
	public function mergeFrom(self|array ...$boxes): self {
		foreach ($boxes as $item) {
			foreach ($item as $k => $v) {
				if (is_numeric($k)) {
					// Numerical, then add
					$this[] = $v;
				} else {
					// String, then replace if exists
					if (!is_null($v)) {
						$this[$k] = $v;
					}
				}
			}
		}

		return $this;
	}

	/**
	 *
	 * TODO Add a flag for case-independent check
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	#[Shortcut('\in_array(key)')]
	public function containsKey(string $key): bool {
		return in_array($key, (array) $this->keys);
	}

	#[Shortcut('\in_array(value)')]
	public function containsValue(mixed $value): bool {
		return in_array($value, (array) $this);
	}

	public function toSet(): Set {
		$class = PHP::redef(Set::class);
		return new $class($this);
	}

	public function toArray(
		bool $recursively = false,
		bool $with_class = false,
		array $exclude_fields = []
	): array {
		return (array) $this;
	}

	/**
	 * Applies callbacks to keys and values
	 *
	 * Keys and values would be modified inside of the box
	 *
	 * The callback will received `$k`, `$v` and `$box` params, and
	 * should return `$k` and `$v` or `null`.
	 * In case `null` is returned, the element will be removed from the box.
	 *
	 * Important: avoid modification of `$box` reference inside the callback,
	 * in the most cases you would receive unexpected result!
	 *
	 * `cb` stands for "callback"
	 *
	 * ```php
	 *  function myCallback($k, $v) {
	 *      if ($v instanceof Version) {
	 *          $v = " ! {$v} ! ";
	 *      }
	 *      return [$k, $v];
	 *  }
	 *
	 *  $res = bx([
	 *      'Test 1' => 'aaa',
	 *      'Test 2' => now(),
	 *      'Test 3' => new Version('1.2.3'),
	 *      'Test 4' => 'bbb',
	 *      'Test 5' => 99,
	 *      100500 => 'text',
	 *      'Test 6' => 100,
	 *  ]);
	 *
	 *  $res->cb('myCallback');
	 *  // From PHP 8.1 you can use shorter callback reference:
	 *  // $res->cb( myCallback(...) );
	 *
	 *  pd("{$res->toJson(true)}");
	 *
	 * ```
	 *
	 * @param callable|array|string ...$callbacks
	 *
	 * @return $this
	 */
	#[Affecting]
	public function cb(callable|array|string ...$callbacks): self {
		foreach ($callbacks as $callback) {
			$callback = $this->clearClosure($callback);

			$to_unset = new static;
			$to_replace = new static;
			foreach ($this as $k => $v) {
				$res = $callback($k, $v, $this);
				if (is_null($res)) {
					$to_unset[] = $k;
				} else {
					$to_replace[$k] = $res;
				}
			}
			foreach ($to_unset as $k) {
				$this->unsetByKey($k);
			}
			foreach ($to_replace as $orig_k => [$k, $v]) {
				if ($k !== $orig_k) {
					$this->unsetByKey($orig_k);
				}
				$this[$k] = $v;
			}
		}
		return $this;
	}

	/**
	 * Applies callbacks to keys
	 *
	 * Inside it uses {@see self::cb()}
	 *
	 * Example:
	 * ```php
	 *  $res = bx([
	 *      'Test 1' => 'aaa',
	 *      'Test 2' => now(),
	 *      'Test 3' => new Version('1.2.3'),
	 *      'Test 4' => 'bbb',
	 *      'Test 5' => 99,
	 *      100500 => 'text',
	 *      'Test 6' => 100,
	 *  ]);
	 *
	 * 	// Or for PHP 8.1 and above:    `$res->cbKeys(Str::upper(...));`
	 *  $res->cbKeys([Str::class, 'upper']);
	 *
	 * ```
	 *
	 *
	 * @param callable|array|string ...$callbacks
	 *
	 * @return $this
	 */
	#[Affecting]
	public function cbKeys(callable|array|string ...$callbacks): self {
		foreach ($callbacks as $callback) {
			$callback = $this->clearClosure($callback);

			$this->cb(function ($k, $v) use ($callback) {
				return [$callback($k), $v];
			});
		}

		return $this;
	}

	/**
	 * Applies callbacks to values
	 *
	 * Inside it uses {@see self::cb()}
	 *
	 * Example:
	 * ```php
	 *  $res = bx([
	 *      'Test 1' => 'aaa',
	 *      'Test 2' => now(),
	 *      'Test 3' => new Version('1.2.3'),
	 *      'Test 4' => 'bbb',
	 *      'Test 5' => 99,
	 *      100500 => 'text',
	 *      'Test 6' => 100,
	 *  ]);
	 *
	 * 	// Or for PHP 8.1 and above:    `$res->cbValues(Str::upper(...));`
	 *  $res->cbValues([Str::class, 'upper']);
	 *
	 * ```
	 *
	 *
	 * @param callable|array|string ...$callbacks
	 *
	 * @return $this
	 */
	#[Affecting]
	public function cbValues(callable|array|string ...$callbacks): self {
		foreach ($callbacks as $callback) {
			$callback = $this->clearClosure($callback);

			$this->cb(function ($k, $v) use ($callback) {
				return [$k, $callback($v)];
			});
		}
		return $this;
	}

	protected function clearClosure($callback) {
		if (is_array($callback) || is_string($callback)) {
			$callback = Closure::fromCallable($callback);
		}
		return $callback;
	}

	/**
	 * Combine keys array and values array
	 *
	 * Example:
	 * ```php
	 *  $res = Box::combine(
	 *      [ 'Raz', 'Dva', 'Tri'],
	 *      [  1.1,   2.2,   3.3 ]
	 *  );
	 * ```
	 *
	 * @param array|Box $keys
	 * @param array|Box $values
	 *
	 * @return static
	 */
	#[Shortcut('\array_combine()')]
	public static function combine(array|Box $keys, array|Box $values): static {
		return new static(array_combine((array) $keys, (array) $values));
	}

	/**
	 * @param int $num
	 *
	 * @return \Generator
	 * @codeCoverageIgnore
	 */
	public function randKeys(int $num = 1): Generator {
		$keys = $this->keys;

		$num = $num < 1
			?1
			:$num;
		foreach (Math::range(0, $num - 1) as $i) {
			yield $keys[Math::rand(0, $keys->size - 1)];
		}
	}

	/**
	 * @param int $num
	 *
	 * @return \Generator
	 * @codeCoverageIgnore
	 */
	public function randValues(int $num = 1): Generator {
		$values = $this->values;

		$num = $num < 1
			?1
			:$num;
		foreach (Math::range(0, $num - 1) as $i) {
			yield $values[Math::rand(0, $values->size - 1)];
		}
	}

	/**
	 * @return false|mixed
	 * @codeCoverageIgnore
	 */
	public function randKey() {
		$keys = $this->keys;
		return $keys[Math::rand(0, $keys->size - 1)];
	}

	/**
	 * @param int $num
	 *
	 * @return false|mixed
	 * @codeCoverageIgnore
	 */
	public function randValue(int $num = 1) {
		$values = $this->values;
		return $values[Math::rand(0, $values->size - 1)];
	}

	/**
	 * Returns value or default
	 *
	 * Basically equivalent of "$box[$key] ?? $default", but allows to search for key-value
	 * in case sensitive (on and off) way.
	 *
	 * @param string|int $key            Key in the array
	 * @param mixed      $default        The default value or null is returned if the key
	 *                                   is not found
	 * @param bool       $case_sensitive Whether the key should be searched in case-sensitive or
	 *                                   case-insensitive way. By default true.
	 *
	 * @return mixed Found value by key or $default (which is null if not specified)
	 */
	public function get(
		string|int $key,
		mixed $default = null,
		bool $case_sensitive = true
	): mixed {
		if (!is_int($key) && !$case_sensitive) {
			$key = Str::lower($key);
			foreach ($this as $k => $v) {
				if (Str::lower($k) === $key) {
					return $v;
				}
			}
			return $default;
		}

		return $this[$key] ?? $default;
	}

	public static array|Box|null $default_sorting = [
		'descending' => false,
		'by_keys' => false,
		'by_values' => true,
		'case_sensitive' => false,
		'natural' => true,
	];

	public array|Box|null $custom_sorting = null;

	protected function _getSortConfig(
		bool $descending = null,
		bool $by_values = null,
		bool $case_sensitive = null,
		bool $natural = null,
		callable $callback = null
	) {
		$default_config = static::$default_sorting;
		$custom_config = $this->custom_sorting;

		$_n = 'descending';
		$$_n = $$_n ?? $custom_config[$_n] ?? $default_config[$_n] ?? false;
		$_n = 'by_values';
		$$_n = $$_n ?? $custom_config[$_n] ?? $default_config[$_n] ?? true;
		$_n = 'case_sensitive';
		$$_n = $$_n ?? $custom_config[$_n] ?? $default_config[$_n] ?? false;
		$_n = 'natural';
		$$_n = $$_n ?? $custom_config[$_n] ?? $default_config[$_n] ?? true;
		$_n = 'callback';
		$$_n = $$_n ?? $custom_config[$_n] ?? $default_config[$_n] ?? null;

		return [$descending, $by_values, $case_sensitive, $natural, $callback];
	}

	/**
	 * @return $this
	 * @codeCoverageIgnore
	 */
	public function shuffle(): self {
		$res = (array) $this;
		shuffle($res);
		$this->exchangeArray($res);
		return $this;
	}

	public function sum(): int|float {
		// TODO Consider usage of BigNumber
		$res = 0;
		foreach ($this as $value) {
			if (!is_int($value) && !is_float($value)) {
				throw new Exception('The value for sum() method neither int, nor float.');
			}
			$res += $value;
		}
		return $res;
	}

	/**
	 * Sort of any kind
	 *
	 * @param bool|null $descending
	 * @param bool|null $by_values
	 * @param bool|null $case_sensitive
	 * @param bool|null $natural
	 * @param callable|null $callback
	 *
	 * FIX  Make sure all the sortings are tested and documented properly
	 * @return self
	 */
	public function sort(
		bool $descending = null,
		bool $by_values = null,
		bool $case_sensitive = null,
		bool $natural = null,
		callable $callback = null
	): self {
		[$descending, $by_values, $case_sensitive, $natural, $callback]
			= $this->_getSortConfig(
				$descending, $by_values, $case_sensitive, $natural, $callback
			);

		$res = (array) $this;
		if ($by_values) {
			// Only by values
			if ($callback) {
				uasort($res, $callback);
			} else {
				$flags = $case_sensitive
					?SORT_FLAG_CASE
					:0;
				if ($natural) {
					if ($case_sensitive) {
						natsort($res);
					} else {
						natcasesort($res);
					}
				} else {
					if ($descending) {
						arsort($res, $flags);
					} else {
						asort($res, $flags);
					}
				}
			}
		} else {
			// Only by keys
			if ($callback) {
				uksort($res, $callback);
			} else {
				$flags = $case_sensitive
					?SORT_FLAG_CASE
					:0;
				if ($descending) {
					krsort($res, $flags);
				} else {
					ksort($res, $flags);
				}
			}
		}
		$this->exchangeArray($res);

		return $this;
	}

	public function implode($sep = ', ') {
		return implode($sep, (array) $this);
	}

	#[Shortcut('\implode()')]
	public function join($sep = ', ') {
		return $this->implode($sep);
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	public static function redefComponentName(): string {
		return InitConfig::REDEF_BOX;
	}

	//// Meta-Magic methods

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

	//// Magic methods

	/**
	 * To debug as a normal array
	 *
	 * @codeCoverageIgnore
	 * @return array
	 * @throws \spaf\simputils\exceptions\InfiniteLoopPreventionExceptions
	 */
	public function __debugInfo(): array {
		return $this->toArray();
	}
}

