<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\logger\Logger;
use spaf\simputils\logger\outputs\CsvFileOutput;
use spaf\simputils\logger\outputs\JsonFileOutput;
use spaf\simputils\logger\outputs\TextFileOutput;
use spaf\simputils\PHP;

/**
 * @covers \spaf\simputils\logger\Logger
 * @covers \spaf\simputils\traits\logger\LoggerBasicOutputTrait
 * @covers \spaf\simputils\logger\outputs\ContextOutput
 * @covers \spaf\simputils\logger\outputs\BasicOutput
 *
 * @uses \spaf\simputils\interfaces\LoggerInterface
 * @uses \spaf\simputils\traits\helpers\DateTimeTrait
 * @uses \spaf\simputils\Settings
 * @uses spaf\simputils\traits\SimpleObjectTrait
 * @uses \spaf\simputils\helpers\DateTimeHelper
 * @uses \spaf\simputils\interfaces\helpers\DateTimeHelperInterface
 * @uses \spaf\simputils\models\DateTime
 * @uses \spaf\simputils\PHP
 *
 */
class LoggerTest extends TestCase {

	public function setUp(): void {
		/** @var Logger $logger */
		Logger::getDefault()->logLevel = Logger::LEVEL_DEBUG;
	}

	public function testDefaultLogging() {
		$ref = 'mr.';
		$year = (int) date('Y');

		ob_start();
		Logger::log('Hello %s world %s', $ref, $year);
		$buffer = ob_get_clean();
		$this->assertMatchesRegularExpression('/.*hello .* world .*/i', $buffer, 'Checking the output');

		ob_start();
		Logger::critical('Hello %s world %s', $ref, $year);
		$buffer = ob_get_clean();
		$this->assertMatchesRegularExpression('/.*hello .* world .*/i', $buffer, 'Checking the output');

		ob_start();
		Logger::error('Hello %s world %s', $ref, $year);
		$buffer = ob_get_clean();
		$this->assertMatchesRegularExpression('/.*hello .* world .*/i', $buffer, 'Checking the output');

		ob_start();
		Logger::warning('Hello %s world %s', $ref, $year);
		$buffer = ob_get_clean();
		$this->assertMatchesRegularExpression('/.*hello .* world .*/i', $buffer, 'Checking the output');

		ob_start();
		Logger::info('Hello %s world %s', $ref, $year);
		$buffer = ob_get_clean();
		$this->assertMatchesRegularExpression('/.*hello .* world .*/i', $buffer, 'Checking the output');

		ob_start();
		Logger::debug('Hello %s world %s', $ref, $year);
		$buffer = ob_get_clean();
		$this->assertMatchesRegularExpression('/.*hello .* world .*/i', $buffer, 'Checking the output');

	}

	public function testLoggerObject() {
		Logger::$default = new Logger('my-tests-shiny-logger');
		ob_start();
		Logger::log('TEST');
		$buffer = ob_get_clean();
		$this->assertMatchesRegularExpression('/.*TEST/i', $buffer, 'Checking the output');
	}

	/**
	 * @covers \spaf\simputils\logger\outputs\CsvFileOutput
	 * @covers \spaf\simputils\logger\outputs\BasicFileOutput
	 * @covers \spaf\simputils\logger\outputs\TextFileOutput
	 * @covers \spaf\simputils\traits\logger\LoggerBasicFileOutputTrait
	 *
	 * @runInSeparateProcess
	 * @return void
	 * @throws \Exception
	 */
	public function testFileOutput() {
		$dir = '/tmp/simputils/tests/logs';
		$prefix = 'tests-log-file-';
		$expected_file = "{$dir}/{$prefix}0.csv";

		// Clearing if exists from previous run
		PHP::rmFile($dir, true);

		$output = new CsvFileOutput($dir, $prefix, 'csv');
		$output->max_file_size = 10;
		Logger::$default = new Logger('my-tests-shiny-logger', [
			$output,
		]);

		Logger::log('Hello World');
		Logger::error('Second line');
		PHP::mkFile($expected_file, 'NOT_CORRECT');
		Logger::error('Third line');
		Logger::error('Fourth line');
		Logger::error('Fifth line');
		Logger::error('Sixth line');
		Logger::info('Seventh line');
		Logger::info('Eighth line');
		Logger::info('Ninth line');
		Logger::info('Tenth line');

		$this->assertFileExists($expected_file);
	}

	/**
	 * @covers \spaf\simputils\logger\outputs\TextFileOutput::getFileLineContent
	 * @covers \spaf\simputils\traits\logger\LoggerBasicFileOutputTrait
	 * @covers \spaf\simputils\logger\outputs\JsonFileOutput
	 * @uses   \spaf\simputils\logger\outputs\BasicFileOutput
	 * @uses   \spaf\simputils\logger\outputs\TextFileOutput
	 *
	 * @runInSeparateProcess
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function testFileContent() {
		$file_path = '/tmp/simputils/tests/__just-a-file.txt';
		PHP::mkFile($file_path, "Some multiline\nContent\nThat must\nbe\nconsidered.");
		$lines = TextFileOutput::getFileLineContent($file_path, 2, 3);
		$this->assertEquals(2, count($lines), 'Array must contain exact amount of lines');
		$this->assertEquals("That must\n", $lines[2], 'First picked up line');
		$this->assertEquals("be\n", $lines[3], 'Second picked up line');

		$lines = TextFileOutput::getFileLineContent($file_path, 2, 10);
		$this->assertEquals(3, count($lines), 'Array must contain exact amount of lines');
		$this->assertEquals("That must\n", $lines[2], 'First picked up line again');
		$this->assertEquals("be\n", $lines[3], 'Second picked up line again');
		$this->assertEquals("considered.", $lines[4], 'Third picked up line');

		$dir = '/tmp/simputils/tests/logs';
		$prefix = 'tests-log-file-';
		$expected_file = "{$dir}/{$prefix}0.json";
		$output = new JsonFileOutput($dir, $prefix, 'json');
		$output->max_file_size = 20;
		Logger::$default = new Logger('testing-text', [
			$output
		]);

		PHP::rmFile('/tmp/simputils/tests', true);

		Logger::error('TEST ok?');
		Logger::critical('Critical message!');
		$output->max_file_size = 1024 * 1024 * 8;
		Logger::critical('Not very Critical message!');

		$this->assertFileExists($expected_file);
		$json_data = json_decode(PHP::getFileContent($expected_file), true);

		$this->assertCount(2, $json_data);

		PHP::rmFile($expected_file);

	}

}