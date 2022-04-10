<?php

namespace spaf\simputils\generic;

use Exception;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\FS;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\Box;
use spaf\simputils\models\DateTimeZone;
use spaf\simputils\models\L10n;
use spaf\simputils\PHP;
use spaf\simputils\special\CommonMemoryCacheIndex;
use spaf\simputils\Str;
use ValueError;
use function is_numeric;

/**
 *
 * @property-read Box|array $successful_init_blocks
 * @property ?L10n $l10n
 * @property ?DateTimeZone $default_tz
 */
abstract class BasicInitConfig extends SimpleObject {

	const REDEF_PD = 'pd';
	const REDEF_PR = 'pr';
	const REDEF_BOX = 'Box';
	const REDEF_DATE_TIME = 'DateTime';
	const REDEF_DATE_TIME_ZONE = 'DateTimeZone';
	const REDEF_DATE_INTERVAL = 'DateInterval';
	const REDEF_DATE_PERIOD = 'DatePeriod';
	const REDEF_DATA_UNIT = 'DataUnit';
	const REDEF_FILE = 'File';
	const REDEF_DIR = 'Dir';
	const REDEF_STACK_FIFO = 'StackFifo';
	const REDEF_STACK_LIFO = 'StackLifo';
	const REDEF_GIT_REPO = 'GitRepo';
	const REDEF_BIG_NUMBER = 'BigNumber';
	const REDEF_PHP_INFO = 'PhpInfo';
	const REDEF_VERSION = 'Version';
	const REDEF_LOGGER = 'Logger';
	const REDEF_L10N = 'L10n';
	const REDEF_TEMPERATURE = 'Temperature';
	const REDEF_SYSTEM_FINGERPRINT = 'SystemFingerprint';
	const REDEF_STR_OBJ = 'StrObj';
	const REDEF_SET = 'Set';

	public ?string $name = null;
	public ?string $code_root = null;
	public ?string $working_dir = null;
	public array|Box $disable_init_for = [];
	protected null|string $_l10n_name = null;
	protected mixed $_l10n = null;

	protected array $_successful_init_blocks = [];
	protected bool $_is_already_setup = false;

	#[Property('default_tz')]
	#[Shortcut('DT::getDefaultTimeZone()')]
	protected function getDefaultTimeZone(): DateTimeZone {
		return DT::getDefaultTimeZone();
	}

	#[Property('default_tz')]
	#[Shortcut('DT::setDefaultTimeZone()')]
	protected function setDefaultTimeZone(string|DateTimeZone $tz) {
		DT::setDefaultTimeZone($tz);
	}

	#[Property('l10n_name')]
	protected function getL10nName(): mixed {
		return $this->_l10n_name;
	}

	#[Property('l10n')]
	protected function getL10n(): mixed {
		return $this->_l10n;
	}

	#[Property('l10n')]
	protected function setL10n(null|string|L10n $val): void {
		if (empty($val)) {
			$this->_l10n_name = $val;
		} else {
			if (Str::is($val)) {
				$this->_l10n_name = $val;

				$class = PHP::redef(L10n::class);
				$l10n_name = Str::upper($val);
				$path = FS::path(PHP::frameworkDir(), 'data', 'l10n', "{$l10n_name}.json");
				$val = $class::createFrom($path);
			}

			/** @var L10n $val */
			if ($val::$is_auto_setup) {
				$val->doSetUp();
			}
		}
		$this->_l10n = $val;
	}

	/**
	 * @return array
	 */
	#[Property('successful_init_blocks')]
	protected function getSuccessfulInitBlocks(): Box|array {
		$class_box = PHP::redef(Box::class);
		return new $class_box($this->_successful_init_blocks);
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
		$box_class = PHP::redef(Box::class);
		$init_blocks = $this->init_blocks;
		if (!$init_blocks instanceof $box_class) {
			$init_blocks = new $box_class($init_blocks);
		}
		return  "{$this->class_short}[name={$this->name}, code_root={$this->code_root}, " .
				"working_dir={$this->working_dir}, init_blocks={$init_blocks}]";
	}
}
