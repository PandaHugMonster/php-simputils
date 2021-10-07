<?php

use PHPUnit\Framework\TestCase;
use spaf\simputils\logger\Logger;
use spaf\simputils\logger\outputs\CsvFileOutput;
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

		$this->assertFileExists($expected_file);
	}

}