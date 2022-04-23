<?php

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\exceptions\InitConfigNonUniqueCodeBlock;
use spaf\simputils\generic\BasicInitConfig;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\interfaces\InitBlockInterface;
use spaf\simputils\models\L10n;
use spaf\simputils\PHP;

class CustomInitCodeBlock extends SimpleObject implements InitBlockInterface {

	/**
	 * @inheritDoc
	 */
	public function initBlock(BasicInitConfig $config) {

	}
}

/**
 * @covers \spaf\simputils\generic\BasicInitConfig
 *
 */
class InitConfigTest extends TestCase {

	/**
	 * @covers \spaf\simputils\models\L10n
	 * @runInSeparateProcess
	 * @return void
	 */
	function testBasics() {
		$config = PHP::init([
			'l10n' => 'RU',
			'default_tz' => 'Asia/Novosibirsk',
			new CustomInitCodeBlock(),
		]);
		$this->assertInstanceOf(L10n::class, $config->l10n);
		$this->assertEquals('RU', $config->l10n->name);
		$this->assertEquals('Asia/Novosibirsk', $config->default_tz);

	}

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	function testNonUniqueInitializedException() {
		$config = PHP::init([
			'l10n' => 'RU',
			'default_tz' => 'Asia/Novosibirsk',
			new CustomInitCodeBlock(),
		]);

		$this->expectException(InitConfigNonUniqueCodeBlock::class);
		PHP::init();
	}
}
