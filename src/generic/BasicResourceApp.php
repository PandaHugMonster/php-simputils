<?php

namespace spaf\simputils\generic;


/**
 *
 */
abstract class BasicResourceApp extends SimpleObject {

	/**
	 * Getting content at once
	 *
	 * @param mixed          $stream Stream/Pointer/FileDescriptor/Path etc.
	 * @param ?BasicResource $file   File instance
	 *
	 * @return mixed
	 */
	abstract public static function getContent(
		mixed $stream,
		?BasicResource $file = null
	): mixed;

	/**
	 * Setting content at once
	 *
	 * @param mixed          $stream Stream/Pointer/FileDescriptor/Path etc.
	 * @param mixed          $data   Data to store
	 * @param ?BasicResource $file   File instance
	 */
	abstract public static function setContent(
		mixed $stream,
		mixed $data,
		?BasicResource $file = null
	): void;

	/**
	 * Default settings for the processor
	 *
	 * Can be empty
	 *
	 * @return mixed
	 */
	public static function defaultProcessorSettings(): mixed {
		return null;
	}

	/**
	 * Getting hierarchically settings from file, if not, then default ones for processor
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
}
