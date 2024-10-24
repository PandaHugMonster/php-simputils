<?php

namespace spaf\simputils\attributes;

use Attribute;
use spaf\simputils\components\RenderedWrapper;
use spaf\simputils\generic\BasicAttribute;
use spaf\simputils\Html;
use spaf\simputils\traits\BaseHtmlTrait;
use spaf\simputils\traits\StaticRendererTrait;

/**
 * Renderer Attribute
 *
 * Basically just a specifier for the rendering functionality.
 *
 * Should be assigned to static methods (no matter `private`, `protected` or `public`),
 * and methods must return either `null` or {@see RenderedWrapper}.
 *
 * @see RenderedWrapper Stringifiable object for
 *      {@see StaticRendererTrait::render()}
 * @see StaticRendererTrait Trait containing `render` method/functionality.
 * @see Html Really minimal HTML static class to create/render tags.
 * @see BaseHtmlTrait Trait containing minimal HTML create/render tag(s)
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Renderer extends BasicAttribute {

}
