<?php
/** @noinspection ALL */

namespace general;

use PHPUnit\Framework\TestCase;
use spaf\simputils\attributes\Renderer;
use spaf\simputils\components\RenderedWrapper;
use spaf\simputils\Html;
use spaf\simputils\models\DateTime;
use spaf\simputils\models\Version;
use spaf\simputils\traits\StaticRendererTrait;
use TypeError;
use function is_object;
use function is_string;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\ts;

class TestingCliRenderingClass extends Html {

	#[Renderer]
	public static function dt(DateTime $value, $args = []): ?RenderedWrapper {
		$value = parent::dt($value, $args);

		return new RenderedWrapper("This is version: {$value}");
	}

	#[Renderer]
	private static function appVersion($value): ?RenderedWrapper {
		if ($value instanceof Version) {
			return new RenderedWrapper("This is version: {$value}");
		}

		return null;
	}

	#[Renderer]
	private static function only_string($value): ?RenderedWrapper {
		if (!is_object($value) && is_string($value)) {
			return new RenderedWrapper("Just a string: {$value}");
		}

		return null;
	}
}

class AnotherRenderingClass {
	use StaticRendererTrait;

	#[Renderer]
	static function incompatibleRenderer($arg1, $arg2, $arg3, $arg4) {
		return new RenderedWrapper("incompatible-renderer: {$arg1}, {$arg2}, {$arg3}, {$arg4}");
	}

	#[Renderer]
	static function compatibleRenderer(...$args) {
		$args = bx($args);
		return new RenderedWrapper("compatible-renderer: {$args}");
	}
}

/**
 * @covers \spaf\simputils\attributes\Renderer
 * @covers \spaf\simputils\components\RenderedWrapper
 * @covers \spaf\simputils\Html
 * @covers \spaf\simputils\traits\BaseHtmlTrait
 * @covers \spaf\simputils\Attrs
 *
 * @uses   \spaf\simputils\PHP
 * @uses   \spaf\simputils\Str
 * @uses   \spaf\simputils\attributes\Property
 * @uses   \spaf\simputils\special\CodeBlocksCacheIndex
 * @uses   \spaf\simputils\traits\PropertiesTrait
 * @uses   \spaf\simputils\models\DateTime
 * @uses   \spaf\simputils\traits\ForOutputsTrait
 * @uses   \spaf\simputils\generic\BasicVersionParser
 * @uses   \spaf\simputils\models\Version
 * @uses   \spaf\simputils\Boolean
 * @uses   \spaf\simputils\basic\bx
 * @uses   \spaf\simputils\models\Box
 * @uses   \spaf\simputils\components\normalizers\BooleanNormalizer
 * @uses   \spaf\simputils\components\normalizers\StringNormalizer
 * @uses   \spaf\simputils\traits\MetaMagic
 */
class RenderersTest extends TestCase {

	function setUp(): void {

	}

	/**
	 * @return void
	 */
	function dataProviderMain() {
		return [
			[
				'This is version: '
				.'<time datetime="2023-01-01T00:00:00+00:00">2023-01-01 00:00:00</time>',
				ts('2023-01-01'),
			],
			['This is version: 1.2.3-DEV', new Version('1.2.3DEV')],
			['Just a string: BEBEBE', 'BEBEBE'],
			['12.3', 12.3],
		];
	}

	/**
	 * @dataProvider dataProviderMain
	 *
	 * @param $expected
	 * @param $value
	 *
	 * @return void
	 * @throws \Exception
	 */
	function testRenderersFunctionality($expected, $value) {
		$r = TestingCliRenderingClass::render($value);
		$this->assertEquals($expected, $r);
	}

	function testRenderersFunctionalityHtml() {
		$r = Html::render('Test');
		$this->assertEquals('Test', $r);

		$r = Html::div('Test');
		$this->assertEquals('<div>Test</div>', $r);

		$r = Html::span('Test');
		$this->assertEquals('<span>Test</span>', $r);

		$this->expectException(TypeError::class);
		$r = Html::dt('Test');
	}

	function testCompatbileIncompatibles() {
		$r = AnotherRenderingClass::render('only first arg');
		$this->assertEquals('compatible-renderer: ["only first arg"]', $r);

		$r = AnotherRenderingClass::render('only first arg', 2);
		$this->assertEquals('compatible-renderer: ["only first arg","2"]', $r);

		$r = AnotherRenderingClass::render('only first arg', 2, true);
		$this->assertEquals('compatible-renderer: ["only first arg","2","1"]', $r);

		$r = AnotherRenderingClass::render('now', 'is', 'incompatible', 'renderer');
		$this->assertEquals('incompatible-renderer: now, is, incompatible, renderer', $r);
	}

}
