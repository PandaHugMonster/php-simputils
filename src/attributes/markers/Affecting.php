<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\generic\BasicAttribute;

/**
 * This attribute should mark affecting methods (those that seriously affect the object itself)
 *
 * It's basically just a marker
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Affecting extends BasicAttribute {

}
