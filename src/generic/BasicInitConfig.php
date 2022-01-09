<?php

namespace spaf\simputils\generic;

use Exception;
use spaf\simputils\attributes\Property;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\Box;
use spaf\simputils\models\InitConfig;
use spaf\simputils\special\CodeBlocksCacheIndex;
use spaf\simputils\special\CommonMemoryCacheIndex;
use ValueError;
use function is_numeric;

/**
 *
 * @property-read Box|array $successful_init_blocks
 */
abstract class BasicInitConfig extends SimpleObject {

	const REDEF_PD = 'pd';
	const REDEF_PR = 'pr';
	const REDEF_BOX = 'Box';
	const REDEF_DATE_TIME = 'DateTime';
	const REDEF_FILE = 'File';
	const REDEF_DIR = 'Dir';
	const REDEF_PHP_INFO = 'PhpInfo';
	const REDEF_VERSION = 'Version';
	const REDEF_LOGGER = 'Logger';

	public ?string $name = null;
	public ?string $code_root = null;
	public ?string $working_dir = null;
	public array|Box $disable_init_for = [];

	protected array $_successful_init_blocks = [];
	protected bool $_is_already_setup = false;


	/**
	 * @return array
	 */
	#[Property('successful_init_blocks')]
	protected function getSuccessfulInitBlocks(): Box|array {
		return new Box($this->_successful_init_blocks);
	}

	/**
	 * @var array|Box|null $init_blocks List of classes FQNs (those classes must implement
	 *                                  interface `\spaf\simputils\interfaces\InitBlockInterface`)
	 */
	public null|array|Box $init_blocks = [];

	/**
	 * @var array|Box|null $redefinitions Key => Closure/Class string
	 */
	public null|array|Box $redefinitions = [];

	public function __construct(?array $args = null) {
		$this->___setup($args ?? []);
	}

	/**
	 * The very first thing is being run, when config successfully registered
	 *
	 * Can be redefined to do initialization/bootstrapping of your stuff
	 *
	 * IMP  Keep in mind to call parent method, to do not disable "init_blocks" invocation.
	 *      If redefined without invoked parent method - then no code of `$init_blocks` would
	 *      be initialized!
	 *
	 * If init-block objects are created and initialized successfully - then those objects added to
	 * `$ran_init_blocks`.
	 *
	 */
	public function init() {
		// The only place getenv is used. It might be safe enough, though not sure yet.
		if (empty($this->name) || $this->name === 'app') {
			$_ENV = CommonMemoryCacheIndex::$initial_get_env_state = !empty($_ENV)
				?$_ENV // @codeCoverageIgnore
				:(getenv() ?? []);
		}

		foreach ($this->init_blocks as $block_class) {
			$orig_obj = null;
			if ($block_class instanceof InitBlockInterface) {
				$orig_obj = $block_class;
				$block_class = $block_class::class;
			}
			if (class_exists($block_class)) {
				if (in_array($block_class, $this->disable_init_for)) {
					continue; // @codeCoverageIgnore
				}

				$init_block_obj = $orig_obj ?? new $block_class;
				/** @var \spaf\simputils\interfaces\InitBlockInterface $init_block_obj */
				if ($init_block_obj->initBlock($this)) {
					$this->_successful_init_blocks[] = $init_block_obj;
				}
			}
		}
		$this->_is_already_setup = true;
	}

	/**
	 * Setting up the InitConfig
	 *
	 * FIX  Changed the modifier to "public" maybe another solution?
	 *
	 * @param array $data Arguments for the object
	 *
	 * @return $this
	 */
	public function ___setup(array $data): static {
		if (!$this->_is_already_setup) {
			foreach ($data as $key => $item) {
				if (is_numeric($key)) {
					if ($item instanceof InitBlockInterface) {
						$this->init_blocks[] = $item;

						// More objects recognition could be added here
					} else {
						throw new ValueError("Not recognized argument: {$item}");
					}
				} else {
					$this->$key = $item;
				}
			}
		} else {
			throw new Exception(
				'The InitConfig object is already setup and initialized.' .
				'It\'s no longer possible to change the setup.'
			);
		}
		return $this;
	}

	public function __toString(): string {
		$box_class = CodeBlocksCacheIndex::getRedefinition(
			InitConfig::REDEF_BOX,
			Box::class
		);
		$init_blocks = $this->init_blocks;
		if (!$init_blocks instanceof $box_class) {
			$init_blocks = new $box_class($init_blocks);
		}
		return  "{$this->class_short}[name={$this->name}, code_root={$this->code_root}, " .
				"working_dir={$this->working_dir}, init_blocks={$init_blocks}]";
	}
}
