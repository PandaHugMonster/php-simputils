<?php

namespace spaf\simputils\models\files\apps;

use Exception;
use spaf\simputils\attributes\Property;
use spaf\simputils\exceptions\ExecutablePermissionException;
use spaf\simputils\exceptions\NotImplementedYet;
use spaf\simputils\FS;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\models\Box;
use spaf\simputils\PHP;

/**
 * PHP Files Processor
 *
 * @property bool $is_box_wrapped
 */
class PHPFileProcessor extends BasicResourceApp {

	#[Property]
	protected bool $_is_box_wrapped = true;

	#[Property(type: 'get')]
	protected bool $_is_fd_supported = false;

	public function getContent(mixed $fd, ?BasicResource $file = null): mixed {
		if (empty($file)) {
			throw new Exception('For this application processor $file instance is required');
		}
		if (!$file->is_executable_processing_enabled) {
			throw new ExecutablePermissionException('Executables like PHP should not be ' .
				'processed through the File infrastructure (except some rare cases)');
		}
		$res = FS::include($file);
		if ($this->_is_box_wrapped && PHP::isArrayCompatible($res)) {
			$class_box = PHP::redef(Box::class);
			return new $class_box($res);
		}
		return $res;
	}

	/**
	 *
	 * @param mixed $fd Stream/Pointer/FileDescriptor/Path etc.
	 * @param mixed $data Data to store
	 * @param ?BasicResource $file File instance
	 *
	 * @throws \spaf\simputils\exceptions\NotImplementedYet
	 */
	public function setContent(mixed $fd, mixed $data, ?BasicResource $file = null): void {
		throw new NotImplementedYet('Saving PHP data to file is not yet supported.');
	}
}
