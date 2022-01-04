<?php

namespace spaf\simputils\components\initblocks;

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\File;
use spaf\simputils\PHP;

/**
 * @property-read ?File $default_file The default file reference (.env) or null if file does
 *                                    not exist.
 */
class DotEnvInitBlock extends SimpleObject implements InitBlockInterface {

	const DEFAULT_FILE_NAME = '.env';

	protected ?File $_default_file = null;

	/**
	 * @codeCoverageIgnore
	 * @return \spaf\simputils\models\File|null
	 */
	#[Property('default_file')]
	protected function getDefaultFile(): ?File {
		return $this->_default_file;
	}

	/**
	 * Initialize all the main DotEnv sub-routines
	 *
	 * @param BasicInitConfig $config Config that initializes this InitBlock
	 *
	 * @return bool
	 */
	public function initBlock(BasicInitConfig $config): bool {
		$res = true;

		$file = new File(static::DEFAULT_FILE_NAME);
		$this->_default_file = $file->exists
			?$file
			:null; // @codeCoverageIgnore

		if (!empty($this->_default_file)) {
			$content = $this->_default_file->content;
			if (!empty($content)) {
				foreach ($content as $key => $value) {
					PHP::envSet($key, $value, false);
				}
			}
		}

		return $res;
	}
}
