<?php

namespace spaf\simputils\models\files\apps\settings;

use Closure;
use spaf\simputils\generic\SimpleObject;
use function is_null;

class CsvSettings extends SimpleObject {

	/**
	 * @var string $separator The delimiter for the csv-file format
	 */
	public string $separator = ',';

	/**
	 * @var string $enclosure The enclosure for the cell
	 */
	public string $enclosure = '"';

	/**
	 * @var string $escape Escape symbol
	 */
	public string $escape = '\\';

	/**
	 * @var bool $first_line_header If it's true, then each row will be parsed as an associative
	 *                              array where key will be taken from header line.
	 *                              If it's false, then each row will be indexed naturally.
	 */
	public bool $first_line_header = true;

	// TODO Maybe implement optional left vertical header as well?

	/**
	 * @var bool $allow_raw_string_saving By default `false` what means it's not allowed
	 *                                    to save data just as a "string" (`false` value considered
	 *                                    as safe and efficient option), array/matrix must be
	 *                                    supplied.
	 *                                    If specified as `true` - then saving "string" value
	 *                                    will not cause exception + the saving itself will be done
	 *                                    through the parent `TextProcessor` functionality.
	 *                                    (this option is considered as unsafe and inefficient,
	 *                                    be careful when using it).
	 */
	public bool $allow_raw_string_saving = false;

	/**
	 * @var \Closure|array|null $postprocessing_callback This is a custom callback that is done
	 *                                                   right after row/line is parsed.
	 *                                                   If null returned, then line will
	 *                                                   be skipped, if data returned - then data
	 *                                                   will be saved instead of initial row/line
	 */
	public null|Closure|array $postprocessing_callback = null;

	function __construct(
		$separator = null,
		$enclosure = null,
		$escape = null,
		$first_line_header = null,
		$allow_raw_string_saving = null,
		$postprocessing_callback = null
	) {
		if (!is_null($separator)) {
			$this->separator = $separator;
		}
		if (!is_null($enclosure)) {
			$this->enclosure = $enclosure;
		}
		if (!is_null($escape)) {
			$this->escape = $escape;
		}
		if (!is_null($first_line_header)) {
			$this->first_line_header = $first_line_header;
		}
		if (!is_null($allow_raw_string_saving)) {
			$this->allow_raw_string_saving = $allow_raw_string_saving;
		}
		if (!is_null($postprocessing_callback)) {
			$this->postprocessing_callback = $postprocessing_callback;
		}
	}
	// TODO Add "null" or "empty" setting, that will be used to fulfill and to be parsed as null
}
