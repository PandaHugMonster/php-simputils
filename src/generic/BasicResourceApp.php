<?php

namespace spaf\simputils\generic;


use spaf\simputils\attributes\Property;

/**
 *
 * @property-read bool $is_fd_supported
 */
abstract class BasicResourceApp extends SimpleObject {

	#[Property]
	protected bool $_is_fd_supported = true;

	/**
	 * Getting content at once
	 *
	 * @param mixed          $fd   Stream/Pointer/FileDescriptor/Path etc.
	 * @param ?BasicResource $file File instance
	 *
	 * @return mixed
	 */
	abstract public function getContent(
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
	abstract public function setContent(
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
	public static function defaultProcessorSettings(): mixed {
		return null;
	}

	/**
	 * Getting hierarchically settings from file, if not, then default ones for processor
	 *
	 * @codeCoverageIgnore
	 *
	 * @param ?BasicResource $file Target file
	 *
	 * @return mixed
	 */
	public static function getSettings(?BasicResource $file = null): mixed {
		if (empty($file)) {
			return static::defaultProcessorSettings();
		}

		return $file->processor_settings ?? static::defaultProcessorSettings();
	}

	public function __invoke(
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
}
