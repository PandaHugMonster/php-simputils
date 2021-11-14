<?php

namespace spaf\simputils\models\files;

use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\NotImplementedYet;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\helpers\FileHelper;
use spaf\simputils\models\files\apps\TextProcessor;
use ValueError;

/**
 *
 * @property ?BasicResourceApp $app
 */
class File extends BasicResource {

	public static array $processors = [
		'text/plain' => TextProcessor::class,
	];

	protected $_app = null;

	/**
	 * @param null|string|resource $file Local File reference
	 *
	 * @note Currently only local files are supported
	 *
	 * @throws \spaf\simputils\exceptions\NotImplementedYet
	 */
	public function __construct(mixed $file = null, $app = null) {
		if (is_null($file)) {
			// In case of a new file, or temp file
			throw new NotImplementedYet();
		} else if (is_resource($file)) {
			throw new NotImplementedYet();
		} else if (!is_string($file)) {
			throw new ValueError('File object can receive only null|string|resource argument');
		}

		// TODO Use string for file. Implement here
		$pi = pathinfo($file);
		$this->_path = $pi['dirname'];
		$this->_name = $pi['filename'];
		$this->_ext = $pi['extension'];
		$this->_mime_type = FileHelper::getFileMimeType($file);
		if (empty($app)) {
			$app = static::$processors[$this->_mime_type] ?? TextProcessor::class;
		}
		$this->_app = new $app();
	}

	#[Property('size')]
	protected function getSize(): int {
		return filesize($this->name_full);
	}

	public function __toString(): string {
		return $this->name_full;
	}

	#[Property('app')]
	protected function getResourceApp(): ?BasicResourceApp {
		return $this->_app;
	}

	#[Property('app')]
	protected function setResourceApp($var): void {
		$this->_app = $var;
	}

	public function getContent() {
		$app = $this->app;
		return $app->getContent($this->name_full);
	}
}
