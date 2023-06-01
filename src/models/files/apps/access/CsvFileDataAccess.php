<?php

namespace spaf\simputils\models\files\apps\access;

use spaf\simputils\attributes\Property;
use spaf\simputils\models\Box;
use spaf\simputils\models\files\apps\settings\CsvSettings;
use spaf\simputils\PHP;
use function fgetcsv;
use function fputcsv;

/**
 * @property-read ?CsvSettings $settings
 */
class CsvFileDataAccess extends TextFileDataAccess {

	#[Property(type: 'get')]
	protected Box|null $_header = null;

	#[Property('settings')]
	protected function getSettings() {
		return $this->app::getSettings($this->file, $this->app->default_settings);
	}

	function header(Box|array|bool $header, $write = false): null|Box|array {

		$this->rewind();

		$res = null;
		if ($write && PHP::isArrayCompatible($header)) {
			$this->writeGroup($header);
			$res = $header;
		} else {
			if ($header === true) {
				$res = $this->readGroup();
			}
		}

		if ($res) {
			$this->_header = PHP::box($res);
		}

		return $this->_header;
	}

	function readGroup(): mixed {
		/** @var CsvSettings $s */
		$fd = $this->_fd;
		$s = $this->app::getSettings($this->file, $this->app->default_settings);
		$res = fgetcsv($fd, 0, $s->separator, $s->enclosure, $s->escape);
		if ($res === false) {
			return null;
		}
		return $res;
	}

	function writeGroup($group): false|int {
		/** @var CsvSettings $s */
		$fd = $this->_fd;
		$s = $this->app::getSettings($this->file, $this->app->default_settings);
		return fputcsv($fd, (array) $group, $s->separator, $s->enclosure, $s->escape);
	}

}
