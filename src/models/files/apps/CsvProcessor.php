<?php

namespace spaf\simputils\models\files\apps;

use Closure;
use spaf\simputils\exceptions\Inconsistent;
use spaf\simputils\exceptions\ParsingProblem;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\models\Box;
use spaf\simputils\models\files\apps\access\CsvFileDataAccess;
use spaf\simputils\models\files\apps\settings\CsvSettings;
use spaf\simputils\PHP;
use spaf\simputils\Str;

/**
 * CSV data processor
 *
 *
 * **Important**: Saving of the data is done in 2 opposite formats that cannot be mixed at once.
 *  1.  **Format one** - no "string" keys allowed, the arrays and sub-arrays are saved as-is (all
 *      the integer keys must be consistent over the whole data-set), the width will be used based
 *      on the most wide row. It's strongly suggested to have such row a very first one and
 *      considering it as a header (It will not affect mechanics of the saving, but it's strongly
 *      recommended to do not create "headless" csv files when possible)
 *  2.  **Format two** - only "string" keys are allowed, and those are considered as "header" part.
 *      These format is position independent, but if the keys are inconsistent, then unknown columns
 *      are added to the end, and all the missing
 *
 * Any breach of the above formats will cause exception to be raised
 *
 * @package spaf\simputils\models\files\apps
 */
class CsvProcessor extends TextProcessor {

	function __construct(CsvSettings|array|Box|null $default_settings = null) {
		if (PHP::isArrayCompatible($default_settings)) {
			$default_settings = new CsvSettings(...$default_settings);
		}
		parent::__construct($default_settings);
	}

	/**
	 * Default settings for the processor
	 *
	 * @return CsvSettings
	 */
	static function defaultProcessorSettings(): CsvSettings {
		return new CsvSettings();
	}

	protected static function wrapWithKeys($line, $header = null) {
		if (!empty($header)) {
			$res = [];
			foreach ($header as $i => $key) {
				$res[$key] = $line[$i];
			}
			$line = $res;
		}

		return $line;
	}

	function getContent(mixed $fd, ?BasicResource $file = null): mixed {
		/** @var CsvSettings $s */
		$s = static::getSettings($file, $this->_default_settings);

//		$box_class = PHP::redef(Box::class);

		$callback = !empty($s->postprocessing_callback)
			?Closure::fromCallable($s->postprocessing_callback)
			:null;
		$res = PHP::box();
		$header = null;
		while (($line = fgetcsv($fd, 0, $s->separator, $s->enclosure, $s->escape)) !== false) {
			if ($s->first_line_header && empty($header)) {
				$header = $line;
				continue;
			}
			$line = static::wrapWithKeys($line, $header);
			if (!empty($callback)) {
				$line = $callback($line);
			}

			if (!empty($line)) {
				$res[] = PHP::box($line);
			}
		}

		return $res;
	}

	/**
	 * Setting content of the file
	 *
	 * NOTE Due to some flexibility, current mechanisms might not be fully efficient (maybe will be
	 *      fixed in the future!)
	 *
	 * @param mixed          $fd   File descriptor
	 * @param mixed          $data Data
	 * @param ?BasicResource $file Resource/File object
	 *
	 */
	function setContent(mixed $fd, mixed $data, ?BasicResource $file = null): void {
		/** @var CsvSettings $s */

		$s = static::getSettings($file, $this->_default_settings);

		if (!is_array($data) && !$data instanceof Box) {
			if ($s->allow_raw_string_saving) {
				parent::setContent($fd, $data, $file);
				return;
			} else {
				throw new ParsingProblem('Data format is not correct. Data must be array/matrix');
			}
		}

		$header = static::prepareHeader($data);
		$header_flipped = null;
		if (!empty($header)) {
			$header_flipped = $header->flipped();
		}

		$is_header_one_set = false;

		foreach ($data as $row) {
			if (!empty($header)) {
				if (!$is_header_one_set) {
					// NOTE Setting the very first line header from keys
					fputcsv($fd, (array) $header, $s->separator, $s->enclosure, $s->escape);
					$is_header_one_set = true;
				}
				$sub_row = [];
				foreach ($header_flipped as $key => $i) {
					$val = null;
					if (!empty($row[$key])) {
						$val = $row[$key];
					}
					$sub_row[$i] = $val;
				}
				$row = $sub_row;
			}

			fputcsv($fd, (array) $row, $s->separator, $s->enclosure, $s->escape);
		}
	}

	/**
	 * Picks up all the keys of the array/matrix for CSV
	 */
	static function prepareHeader(array|Box $data): null|Box {
//		$class_box = PHP::redef(Box::class);

		$is_box_used = $data instanceof Box && PHP::$use_box_instead_of_array;
		$is_assoc_used = false;
		$is_index_used = false;
//		$box_class = PHP::redef(Box::class);

		$res = $is_box_used
			?PHP::box()
			:[];
		// NOTE CSV array - basically means matrix
		foreach ($data as $row) {
			foreach ($row as $key => $val) {
				// NOTE Mix up check (In case of mix up, exception is raised here)
				static::_checkMixUpOfKeys($key, $is_index_used, $is_assoc_used);

				// NOTE Using in such way to simulate "sets" behaviour
				if ($is_assoc_used) {
					// In case of assoc
					$res[$key] = $key;
				} else {
					// In case of indices - no header
					$res = null;
				}
			}
		}
		return empty($res)
			?null
			:($res instanceof Box
				?$res->values
				:PHP::box(array_values($res)));
	}

	protected static function _checkMixUpOfKeys($key, &$is_index_used, &$is_assoc_used) {
		// TODO Make sure check is tested and works

		if (is_integer($key)) {
			$is_index_used = true;
		}

		if (Str::is($key)) {
			$is_assoc_used = true;
		}
		if ($is_assoc_used && $is_index_used) {
			throw new Inconsistent(
				'Both assoc and indices are used. '.
				'Please use just one option, do not mix up.'
			);
		}
	}

	function fileDataAccessObj($file, $fd = null, $is_opened_locally = false) {
		return new CsvFileDataAccess($file, $this, $fd, $is_opened_locally);
	}
}
