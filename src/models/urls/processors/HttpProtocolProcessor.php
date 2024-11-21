<?php

namespace spaf\simputils\models\urls\processors;

use spaf\simputils\attributes\markers\Deprecated;

/**
 *
 * TODO It was left for compatibility reasons, starting from SimpUtils 2.0.0 version,
 *      it will be removed
 *
 * @deprecated Please use \spaf\simputils\models\urls\processors\HttpSchemeProcessor instead
 */
#[Deprecated(
	'Wrong naming "protocol" instead of commonly used "scheme"',
	'\spaf\simputils\models\urls\processors\HttpSchemeProcessor'
)]
class HttpProtocolProcessor extends HttpSchemeProcessor {


}
