<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\attributes\markers\Shortcut;
use spaf\simputils\attributes\Property;
use spaf\simputils\DT;
use spaf\simputils\exceptions\InitConfigAlreadyInitialized;
use spaf\simputils\FS;
use spaf\simputils\interfaces\ExecEnvHandlerInterface;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\BigNumber;
use spaf\simputils\models\Box;
use spaf\simputils\models\DataUnit;
use spaf\simputils\models\DateTimeZone;
use spaf\simputils\models\L10n;
use spaf\simputils\PHP;
use spaf\simputils\special\CommonMemoryCacheIndex;
use spaf\simputils\Str;
use ValueError;
use function is_array;
use function is_null;
use function is_numeric;
use function is_string;

/**
 *
 * @property-read Box|array $successful_init_blocks
 * @property ?L10n $l10n
 * @property ?DateTimeZone $default_tz
 *
 * @property string $big_number_extension
 * @property bool $data_unit_long
 * @property null|ExecEnvHandlerInterface|\spaf\simputils\generic\BasicExecEnvHandler $ee Exec-Environment
 */
abstract class BasicInitConfig extends SimpleObject {

	const REDEF_PD = 'pd';
	const REDEF_PR = 'pr';
	const REDEF_BOX = 'Box';
	const REDEF_BRO = 'BoxRO';
	const REDEF_DATE_TIME = 'DateTime';
	const REDEF_DATE_TIME_ZONE = 'DateTimeZone';
	const REDEF_DATE_INTERVAL = 'DateInterval';
	const REDEF_DATE_PERIOD = 'DatePeriod';
	const REDEF_TIME_DURATION = 'TimeDuration';
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
	const REDEF_IPV4_RANGE = 'IPv4Range';
	const REDEF_IPV4 = 'IPv4';
	const REDEF_URL = 'UrlObject';

	public ?string $name = null;
	public ?string $default_host = 'localhost';
	public ?string $code_root = null;
	public ?string $working_dir = null;
	public array|Box $disable_init_for = [];

	public null|array|Box $allowed_data_dirs = [];

	protected bool $_is_timezone_changed = false;

	#[Property('ee', type: 'get')]
	protected ?ExecEnvHandlerInterface $_ee_handler = null;

	#[Property('ee')]
	protected function setEe(null|ExecEnvHandlerInterface|array|Box|string $val) {
		$obj = null;

		if ($val instanceof ExecEnvHandlerInterface) {
			$obj = $val;
		} else if (is_string($val)) {
			$obj = new BasicExecEnvHandler($val);
		} else if ($val instanceof Box || is_array($val)) {
			$obj = new BasicExecEnvHandler(...$val);
		}

		$this->_ee_handler = $obj;
	}

	#[DebugHide]
	protected null|string $_l10n_name = null;
	#[DebugHide]
	protected mixed $_l10n = null;

	#[DebugHide]
	protected array $_successful_init_blocks = [];
	#[DebugHide]
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
		$this->_is_timezone_changed = true;
	}

	#[Property('l10n')]
	protected function getL10n(): mixed {
		return $this->_l10n;
	}

	/** @noinspection PhpUndefinedMethodInspection */
	#[Property('l10n')]
	protected function setL10n(null|string|L10n $val): void {
		$preserved_tz = null;
		if ($this->_is_timezone_changed) {
			$preserved_tz = $this->default_tz;
		}
		if (empty($val)) {
			$this->_l10n_name = $val; // @codeCoverageIgnore
		} else {
			if (Str::is($val)) {

				$class = PHP::redef(L10n::class);
				$l10n_name = Str::upper($val);
				// MARK Implement through the newly introduced "FS::data"
				$path = FS::path(PHP::frameworkDir(), 'data', 'l10n', "{$l10n_name}.json");
				/** @var L10n $val */
				$val = $class::createFrom($path);

				$custom_file = FS::file(
					FS::path($this->working_dir, 'data', 'l10n', "{$l10n_name}.json")
				);
				if ($custom_file->exists) {
					PHP::metaMagicSpell( // @codeCoverageIgnore
						$val, 'setup', $custom_file->content ?? [] // @codeCoverageIgnore
					); // @codeCoverageIgnore
				}
				$val->name = $l10n_name;
			}

			/** @var L10n $val */
			if ($val::$is_auto_setup) {
				$val->doSetUp();
			}
		}
		$this->_l10n = $val;
		if (!empty($preserved_tz)) {
			$this->default_tz = $preserved_tz;
		}
	}

	/**
	 * @codeCoverageIgnore
	 * @return \spaf\simputils\models\Box|array
	 * @throws \spaf\simputils\exceptions\RedefUnimplemented Redefinable component is not defined
	 */
	#[Property('successful_init_blocks')]
	protected function getSuccessfulInitBlocks(): Box|array {
		return PHP::box($this->_successful_init_blocks);
	}

	/**
	 * @param string $val Name of the extension that should be used by default
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('big_number_extension')]
	#[Shortcut('BigNumber::$default_extension')]
	protected function setBigNumberExt(string $val) {
		 BigNumber::$default_extension = $val;
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	#[Property('big_number_extension')]
	#[Shortcut('BigNumber::$default_extension')]
	protected function getBigNumberExt(): string {
		return BigNumber::$default_extension;
	}

	/**
	 * @param bool $val If set to true, then long format is used for DataUnit otherwise short
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	#[Property('data_unit_long')]
	#[Shortcut('DataUnit::$long_format')]
	protected function setDataUnitLong(bool $val) {
		DataUnit::$long_format = $val;
	}

	/**
	 * @codeCoverageIgnore
	 * @return bool
	 */
	#[Property('data_unit_long')]
	#[Shortcut('DataUnit::$long_format')]
	protected function getDataUnitLong(): bool {
		return DataUnit::$long_format;
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

	public function __construct(null|array|Box $args = null) {
//		$this->___setup($args ?? []);
		PHP::metaMagicSpell($this, 'setup', $args ?? []);
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
				?$_ENV:(getenv() ?? []); // @codeCoverageIgnore
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
				$init_res = $init_block_obj->initBlock($this);
				if (is_null($init_res) || $init_res == true) {
					$this->_successful_init_blocks[] = $init_block_obj;
				}
			}
		}
		$this->_is_already_setup = true;
	}

	/**
	 * Setting up the InitConfig
	 *
	 * TODO Changed the modifier to "public" maybe another solution?
	 *
	 * @param array|Box $data Arguments for the object
	 *
	 * @return $this
	 * @throws \spaf\simputils\exceptions\InitConfigAlreadyInitialized Already initialized
	 */
	public function ___setup(array|Box $data): static {
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

			// This is important to do not consider default l10n setting of tz as "tz changed"
			$this->_is_timezone_changed = false;

			if (isset($data['default_tz'])) {
				// NOTE Important to do it so, because otherwise the "l10n" value will
				//      override it depending on the order of the config array
				$this->default_tz = $data['default_tz'];
			}
		} else {
			throw new InitConfigAlreadyInitialized( // @codeCoverageIgnore
				'The InitConfig object is already setup and initialized.' .
				'It\'s not possible to initialize it more than once.'
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
