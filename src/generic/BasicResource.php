<?php

namespace spaf\simputils\generic;

use Closure;
use spaf\simputils\attributes\Property;
use spaf\simputils\Data;
use spaf\simputils\FS;
use spaf\simputils\models\Box;
use spaf\simputils\models\files\apps\CsvProcessor;
use spaf\simputils\models\files\apps\DotenvProcessor;
use spaf\simputils\models\files\apps\JsonProcessor;
use spaf\simputils\models\files\apps\TextProcessor;

/**
 * Basic resource abstract model
 * TODO Currently only "local" resources/files are supported. In the future it will be extended
 * FIX  The architecture must be reviewed and adjusted before release
 *
 * @property-read ?string $mime_type
 * @property-read int $size Size in bytes
 * @property-read ?string $size_hr Human readable size string
 * @property-read ?string $extension
 * @property-read ?string $name
 * @property-read ?string $name_full
 * @property-read ?string $path
 * @property-read bool $is_local
 * @property-read string $urn
 * @property-read string $uri
 *
 * @property-read ?string $md5
 * @property-read ?resource $fd
 * @property-read BasicResourceApp|callable|null $app
 *
 */
abstract class BasicResource extends SimpleObject {

	public static Box|array $processors = [
		// Generic text processor
		'text/plain' => TextProcessor::class,

		// JSON processors
		'application/json' => JsonProcessor::class,

		// CSV processors
		'text/csv' => CsvProcessor::class,
		'application/csv' => CsvProcessor::class,

		// DotEnv processor
		'text/dotenv' => DotenvProcessor::class,
		'application/dotenv' => DotenvProcessor::class,
	];

	protected static $processors_index = null;

	public mixed $processor_settings = null;

	protected ?string $_urn = null;
	protected bool $_is_local = true;
	protected ?string $_path = null;
	protected ?string $_name = null;
	protected ?string $_ext = null;
	protected ?int $_size = null;
	protected ?string $_mime_type = null;
	protected ?string $_md5 = null;
	protected mixed $_fd = null;

	/**
	 * Returns ResourceApp object for a particular mime-type/file-type
	 *
	 * Both params are optional to help identify the app-class better
	 *
	 * If you want to help identify the correct type, providing even "potential" filename
	 * would improve the identification process.
	 *
	 * If no params are supplied, you will get object of the default ResourceApp,
	 * which is usually `TextProcessor`
	 *
	 * @param ?string $file_name File name
	 * @param ?string $mime      Mime type
	 *
	 * @see \spaf\simputils\generic\BasicResourceApp
	 * @see TextProcessor
	 *
	 * @return BasicResourceApp|TextProcessor
	 */
	public static function getCorrespondingProcessor(
		?string $file_name = null,
		?string $mime = null,
	): BasicResourceApp|TextProcessor {
		$mime = $mime ?? (!empty($file_name)?FS::getFileMimeType($file_name):null);

		$class = static::$processors[$mime] ?? TextProcessor::class;

		if (empty(static::$processors_index[$class])) {
			static::$processors_index[$class] = new $class();
		}

		return static::$processors_index[$class];
	}

	#[Property('fd')]
	protected function getFd(): mixed {
		return $this->_fd;
	}

	#[Property('uri')]
	protected function getUri(): ?string {
		return $this->urn;
	}

	#[Property('mime_type')]
	protected function getMimeType(): ?string {
		return $this->_mime_type;
	}

	#[Property('md5')]
	protected function getMd5(): ?string {
		return $this->_md5;
	}

	#[Property('size')]
	protected function getSize(): ?int {
		return $this->_size;
	}

	#[Property('size_hr')]
	protected function getSizeHuman(): ?string {
		return Data::humanReadable($this->size ?? 0);
	}

	#[Property('extension')]
	protected function getExtension(): ?string {
		return $this->_ext;
	}

	#[Property('name')]
	protected function getName(): ?string {
		return $this->_name;
	}

	#[Property('name_full')]
	protected function getNameFull(): ?string {
		if (empty($this->_path) && empty($this->_name) && empty($this->_ext)) {
			return null;
		}
		return FS::glueFullFilePath($this->_path, $this->_name, $this->_ext);
	}

	#[Property('path')]
	protected function getPath(): ?string {
		return $this->_path;
	}

	#[Property('is_local')]
	protected function getIsLocal(): bool {
		return $this->_is_local;
	}

	#[Property('urn')]
	protected function getUrn(): string {
		return 'urn:'.$this->_urn;
	}

	#[Property('app')]
	abstract protected function getResourceApp(): null|Closure|array|BasicResourceApp;

	#[Property('app')]
	abstract protected function setResourceApp(null|Closure|array|BasicResourceApp $var): void;
}
