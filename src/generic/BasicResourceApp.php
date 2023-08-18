<?php

namespace spaf\simputils\generic;


use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\NotImplemented;

/**
 *
 * @property-read bool $is_fd_supported
 * @property-read bool $default_settings
 */
abstract class BasicResourceApp extends SimpleObject {

	#[Property]
	protected bool $_is_fd_supported = true;

	#[Property(type: 'get')]
	protected $_default_settings = null;

	function __construct($default_settings = null) {
		$this->_default_settings = $default_settings;
	}

	/**
	 * Getting content at once
	 *
	 * @param mixed          $fd   Stream/Pointer/FileDescriptor/Path etc.
	 * @param ?BasicResource $file File instance
	 *
	 * @return mixed
	 */
	abstract function getContent(
		mixed $fd,
		?BasicResource $file = null
	): mixed;

	/**
	 * Setting content at once
	 *
	 * @param mixed          $fd   Stream/Pointer/FileDescriptor/Path etc.
	 * @param mixed          $data Data to store
	 * @param ?BasicResource $file File instance
	 */
	abstract function setContent(
		mixed $fd,
		mixed $data,
		?BasicResource $file = null
	): void;

	/**
	 * Default settings for the processor
	 *
	 * Can be empty
	 *
	 * @codeCoverageIgnore
	 *
	 * @return mixed
	 */
	static function defaultProcessorSettings(): mixed {
		return null;
	}

	/**
	 * Getting hierarchically settings from file, if not, then default ones for processor
	 *
	 * @codeCoverageIgnore
	 *
	 * @param ?BasicResource $file Target file
	 * @param mixed          $app_default_settings
	 *
	 * @return mixed
	 */
	static function getSettings(
		?BasicResource $file = null,
		mixed $app_default_settings = null
	): mixed {
		$app_settings = $app_default_settings ?? static::defaultProcessorSettings();
		if (empty($file)) {
			return $app_settings;
		}

		return $file->processor_settings ?? $app_settings;
	}

	function __invoke(
		BasicResource $file,
		$fd,
		bool $is_reading = true,
		mixed $data = null
	) {
		if ($is_reading) {
			return $this->getContent($fd, $file);
		}

		$this->setContent($fd, $data, $file);
	}

	function fileDataAccessObj($file, $fd = null, $is_opened_locally = false) {
		throw new NotImplemented();
	}

}
