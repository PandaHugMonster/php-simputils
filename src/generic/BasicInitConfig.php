<?php

namespace spaf\simputils\generic;

use spaf\simputils\models\Box;
use spaf\simputils\models\InitConfig;
use spaf\simputils\special\CodeBlocksCacheIndex;
use spaf\simputils\special\CommonMemoryCacheIndex;

/**
 *
 */
abstract class BasicInitConfig extends SimpleObject {

	const REDEF_PD = 'pd';
	const REDEF_BOX = 'Box';
	const REDEF_DATE_TIME = 'DateTime';
	const REDEF_FILE = 'File';
	const REDEF_PHP_INFO = 'PhpInfo';
	const REDEF_VERSION = 'Version';
	const REDEF_LOGGER = 'Logger';

	public ?string $name = null;
	public ?string $code_root = null;
	public ?string $working_dir = null;
	public array|Box $disable_init_for = [];

	protected array $successful_init_blocks = [];

	/**
	 * @var array|Box|null $init_blocks List of classes FQNs (those classes must implement
	 *                                  interface `\spaf\simputils\interfaces\InitBlockInterface`)
	 */
	public null|array|Box $init_blocks = [];

	/**
	 * @var array|Box|null $redefinitions Key => Closure/Class string
	 */
	public null|array|Box $redefinitions = [];

	public function __construct(mixed ...$params) {
		foreach ($params as $key => $val) {
			$this->$key = $val;
		}
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
			if (class_exists($block_class)) {
				if (in_array($block_class, $this->disable_init_for)) {
					continue; // @codeCoverageIgnore
				}

				$init_block_obj = new $block_class();
				/** @var \spaf\simputils\interfaces\InitBlockInterface $init_block_obj */
				if ($init_block_obj->initBlock($this)) {
					$this->successful_init_blocks[] = $init_block_obj;
				}
			}
		}
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
