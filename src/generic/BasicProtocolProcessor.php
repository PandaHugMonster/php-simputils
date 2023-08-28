<?php

namespace spaf\simputils\generic;

use spaf\simputils\attributes\markers\Deprecated;

/**
 * @property-read ?string $protocol
 * @deprecated
 */
#[Deprecated(
	'Wrong naming "protocol" instead of commonly used "scheme"',
	'\spaf\simputils\generic\BasicSchemeProcessor'
)]
abstract class BasicProtocolProcessor extends BasicSchemeProcessor {

}
