<?php

namespace general;

use Exception;
use PHPUnit\Framework\TestCase;
use spaf\simputils\models\Box;
use spaf\simputils\models\files\apps\CsvProcessor;
use spaf\simputils\models\files\apps\DotEnvProcessor;
use spaf\simputils\models\files\apps\JsonProcessor;
use spaf\simputils\models\files\apps\settings\CsvSettings;
use spaf\simputils\models\files\apps\settings\DotEnvSettings;
use spaf\simputils\models\files\apps\TextProcessor;
use spaf\simputils\PHP;
use spaf\simputils\special\dotenv\ExtInclude;
use spaf\simputils\special\dotenv\ExtMetaData;
use spaf\simputils\special\dotenv\ExtTypeHint;
use function file_get_contents;
use function file_put_contents;
use function spaf\simputils\basic\box;
use function spaf\simputils\basic\fl;

/**
 * @covers \spaf\simputils\models\files\apps\JsonProcessor
 * @covers \spaf\simputils\models\files\apps\TextProcessor
 * @covers \spaf\simputils\models\files\apps\DotEnvProcessor
 * @covers \spaf\simputils\models\files\apps\CsvProcessor
 *
 * @covers \spaf\simputils\models\files\apps\settings\DotEnvSettings
 *
 * @uses \spaf\simputils\PHP
 * @uses \spaf\simputils\generic\BasicResource
 * @uses \spaf\simputils\models\File
 * @uses \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses \spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\generic\BasicResourceApp
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__get
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__set
 * @uses \spaf\simputils\traits\SimpleObjectTrait::__isset
 * @uses \spaf\simputils\models\Box
 * @uses \spaf\simputils\FS
 * @uses \spaf\simputils\attributes\Property
 * @uses \spaf\simputils\basic\box
 * @uses \spaf\simputils\traits\SimpleObjectTrait::____prepareProperty
 * @uses \spaf\simputils\basic\fl
 */
class DefaultAppProcessorsTest extends TestCase {

	/**
	 * @return void
	 */
	function testJsonProcessor() {
		$file = PHP::file(app: JsonProcessor::class);

		$file->content = [
			'code' => 'some funny code',
		];

		$this->assertArrayHasKey('code', $file->content);
		$this->assertIsArray($file->content);
	}

	/**
	 * @covers \spaf\simputils\generic\BasicDotEnvCommentExt
	 * @covers \spaf\simputils\special\dotenv\ExtInclude
	 * @covers \spaf\simputils\special\dotenv\ExtMetaData
	 * @covers \spaf\simputils\special\dotenv\ExtTypeHint
	 * @return void
	 */
	function testDotEnvProcessor() {
		$file = PHP::file(app: DotEnvProcessor::class);

		$file->content = [
			'CODE 1' => 'AGAiN',
		];

		$this->assertArrayHasKey('CODE_1', $file->content);
		$this->assertIsArray($file->content);

		$target_file_path = '/tmp/test-dot-env-file-unittestsss.env';
		$file = fl($target_file_path);
		$file->content = [
			'PARAM_100500' => 'test',
			null,
			'',
			'DOT_DOT_1' => 'kot',
		];
		$this->assertFileExists($target_file_path);
		$example = "PARAM_100500=\"test\"\n#\t\n#\t\nDOT_DOT_1=\"kot\"";
		$this->assertEquals($example, file_get_contents($target_file_path));

		$replace_content_with = "PARAM_1=\"Value 1\"\n\n\nPARAM_2=";
		file_put_contents($target_file_path, $replace_content_with);

		$this->assertEquals(['PARAM_1' => 'Value 1'], $file->content);

		$file->processor_settings = new DotEnvSettings();
		$file->processor_settings->auto_type_hinting = true;
		$file->processor_settings->show_comments = true;

		$file->content = [
			'new param 1' => 1,
			new ExtInclude('/tmp/file/stuff.txt'),
			new ExtMetaData(name: 'UnitTestsStuff', author: 'Pandytch'),
			'HO HO ho' => new ExtTypeHint('mixed'),
			new ExtTypeHint('int'),
			'12boooooo' => 12,
			'onemore' => "'12'",
		];

		$example = [
			'#:#	type-hint integer',
			'NEW_PARAM_1' => '1',
			'#:	include /tmp/file/stuff.txt',
			'#:	meta-data {"name":"UnitTestsStuff","author":"Pandytch"}',
			'#:#	type-hint mixed',
			'#:#	type-hint int',
			'#:#	type-hint integer',
			'_12BOOOOOO' => '12',
			'#:#	type-hint string',
			'ONEMORE' => '12',
		];
		$this->assertEquals($example, $file->content);

		$file = fl($target_file_path, TextProcessor::class);
		$file->content = "NEW_PARAM_1=1\nBOOOOOO=12 # Comment here\nDOOOO=45.4";

		$file = fl($target_file_path, DotEnvProcessor::class);
		$example = [
			'NEW_PARAM_1' => '1',
			'BOOOOOO' => '12',
			'DOOOO' => '45.4',
		];
		$this->assertEquals($example, $file->content);

	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testDotEnvProcessorException() {
		$this->expectException(Exception::class);

		$file = fl('/tmp/test-dot-env-file-unittestsss.env');
		$file->content = [
			'in-line-param-with-non-inline-extension' => new ExtMetaData(name: 'Wut?'),
		];
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testDotEnvProcessorSettingsException1() {
		$this->expectException(Exception::class);

		$file = fl('/tmp/test-dot-env-file-unittestsss.env');
		$file->processor_settings = new DotEnvSettings();
		$file->processor_settings->enforce_letter_case = 'non-existing-letter-case';

		$this->expectException(Exception::class);

		$file->content = [
			'test',
			'test' => 'test',
		];
	}

	/**
	 * @return void
	 */
	function testCsvProcessor() {

		$file = PHP::file(app: CsvProcessor::class);

		$example = box([
			box(['col1' => 'AGAiN', 'col2' => 12, 'col3' => 55]),
			box(['col1' => 'DOTDOT', 'col2' => 77, 'col3' => 99]),
		]);
		$file->content = $example;

		$this->assertEquals(77, $file->content[1]['col2']);
		$this->assertInstanceOf(Box::class, $file->content);
		$this->assertEquals(2, $file->content->size);

		// Custom settings

		$settings = new CsvSettings();
		$settings->postprocessing_callback = function ($data) {
			echo "All good!\n";
		};

		$file = PHP::file(
			'/tmp/csv-test-file-example-bla-bla-bla.csv', app: CsvProcessor::class
		);
		$file->processor_settings = $settings;
		$file->content = $example;

		$content = $file->content;

		$this->expectOutputString("All good!\nAll good!\n");

		$file = PHP::file('/tmp/csv-test-file-example-bla-bla-bla.csv');

		$file->content = [
			['head1', 'head2', 'head3', 'head4'],
			['Value1', 'Value2', 'Value3', 'Value4'],
			['Value5', 'Value6', 'Value7', 'Value8'],
			['Value9', 'Value10', 'Value11', 'Value12'],
		];

		$content = $file->content;
		$this->assertEquals('Value3', $content[0]['head3']);
		$this->assertEquals('Value6', $content[1]['head2']);
		$this->assertEquals('Value12', $content[2]['head4']);

		$settings = new CsvSettings();
		$settings->allow_raw_string_saving = true;

		$file = PHP::file(
			'/tmp/csv-test-file-example-bla-bla-bla.csv', app: CsvProcessor::class
		);
		$file->processor_settings = $settings;
		$example = 'THIS IS INVALID CSV FORMAT, AND STILL SAVED';
		$file->content = $example;

		$this->assertEquals($example, file_get_contents($file->name_full));

	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testCsvProcessorExceptionKeysMix() {
		$file = PHP::file('/tmp/csv-test-file-example-bla-bla-bla.csv');

		$this->expectException(Exception::class);

		$file->content = [
			['head1', 'head2', 'head3', 'head4'],
			['dver' => 'Value1', 'Value2', 'Value3', 'Value4'],
		];
	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testCsvProcessorExceptionWrongDataTypeOfContent() {
		$file = PHP::file('/tmp/csv-test-file-example-bla-bla-bla.csv');

		$this->expectException(Exception::class);

		$file->content = "stroka";
	}
}
