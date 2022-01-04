<?php

namespace spaf\simputils\special\dotenv;

use spaf\simputils\generic\BasicDotEnvCommentExt;
use function spaf\simputils\basic\box;

/**
 *
 * FIX  Improve format!
 *
 * @codeCoverageIgnore
 */
class ExtMetaData extends BasicDotEnvCommentExt {

	public function getPrefix(): string {
		return static::PREFIX_GLOBAL;
	}

	public static function getName(): string {
		return 'meta-data';
	}

	public function __construct(
		public ?string $name = null,
		public ?string $description = null,
		public ?string $author = null,
		public ?string $contact = null,
		public ?string $url = null,
		public ?string $version = null,
	) {}

	public function params(): ?string {
		$res = box([]);

		if ($this->name) {
			$res['name'] = $this->name;
		}
		if ($this->description) {
			$res['description'] = $this->description;
		}
		if ($this->author) {
			$res['author'] = $this->author;
		}
		if ($this->contact) {
			$res['contact'] = $this->contact;
		}
		if ($this->url) {
			$res['url'] = $this->url;
		}
		if ($this->version) {
			$res['version'] = $this->version;
		}

		return $res;
	}

	/**
	 * Parser
	 *
	 * If successfully parsed returns fulfilled object, otherwise false.
	 *
	 * **Important:** If the content seems unfitting (for example the structure is for another
	 * extension) - then must be returned false, so the next registered dotenv comment extension
	 * would be able to try out on this line.
	 *
	 * @param string $value Parsable dotenv comment-extension string
	 *
	 * @return false|static Returns object of the same time if parsed successfully, or false
	 *                      if the string is not fitting to this class
	 */
	public static function parse(string $value): static|false {
		$res = false;

		return $res;
	}
}
