<?php

namespace general\bugs;

use PHPUnit\Framework\TestCase;
use spaf\simputils\DT;
use spaf\simputils\models\DateTime;
use spaf\simputils\PHP;

/**
 * @covers \spaf\simputils\DT
 * @covers \spaf\simputils\models\DateTime
 *
 * @uses \spaf\simputils\Boolean
 * @uses \spaf\simputils\FS
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\Str
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\basic\bx
 * @uses \spaf\simputils\components\initblocks\DotEnvInitBlock
 * @uses \spaf\simputils\components\normalizers\BooleanNormalizer
 * @uses \spaf\simputils\generic\BasicExecEnvHandler
 * @uses \spaf\simputils\generic\BasicInitConfig
 * @uses \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\models\File
 * @uses \spaf\simputils\models\L10n
 * @uses \spaf\simputils\models\files\apps\JsonProcessor
 * @uses \spaf\simputils\models\files\apps\TextProcessor
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\MetaMagic
 * @uses \spaf\simputils\traits\PropertiesTrait
 * @uses \spaf\simputils\components\normalizers\StringNormalizer
 */
class DateTimeNormalizationIssues202307Test extends TestCase {

	function testAutoDateTimeParsing() {
		// FIX  Unfinished, more testing is required!
		PHP::init([
			"l10n" => "IT",
		]);
		$ts = DT::ts('25/12/2022 05:04:03');
	 	$this->assertInstanceOf(DateTime::class, $ts);
	}
}
