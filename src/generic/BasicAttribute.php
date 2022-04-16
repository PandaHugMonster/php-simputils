<?php

namespace spaf\simputils\generic;

use Attribute;

/**
 * @codeCoverageIgnore
 * TODO Implement some caching index for all the attribute-objects! So an attribute-object
 *      is created just once for each case. Currently some of the processes are not optimized (
 *      for example {@see \spaf\simputils\traits\PropertiesTrait::___extractFields}).
 *      + it would be useful to refactor and improve {@see \spaf\simputils\attributes\Property}
 *      and {@see \spaf\simputils\attributes\PropertyBatch}
 */
#[Attribute]
abstract class BasicAttribute {


}
