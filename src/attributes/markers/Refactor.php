<?php

namespace spaf\simputils\attributes\markers;

use Attribute;
use spaf\simputils\generic\BasicInspectionMarker;

/**
 * Refactoring marking
 *
 * @property ?string $comment
 */
#[Attribute(Attribute::IS_REPEATABLE)]
class Refactor extends BasicInspectionMarker {


}
