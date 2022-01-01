<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\Property;
use spaf\simputils\FS;
use spaf\simputils\helpers\DataHelper;

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
 *
 */
abstract class BasicResource extends SimpleObject {

	public mixed $processor_settings = null;

	protected ?string $_urn = null;
	protected bool $_is_local = true;
	protected ?string $_path = null;
	protected ?string $_name = null;
	protected ?string $_ext = null;
	protected ?int $_size = null;
	protected ?string $_mime_type = null;
	protected ?string $_md5 = null;

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
		return DataHelper::humanReadable($this->size);
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
	abstract protected function getResourceApp(): ?BasicResourceApp;

	#[Property('app')]
	abstract protected function setResourceApp($var): void;
}
