<?php

namespace spaf\simputils\generic\constants;

/**
 * System Fingerprint constants
 *
 * @see \spaf\simputils\components\DefaultSystemFingerprint
 */
interface ConstSystemFingerprint {

	const ALGO_MD5 = 'md5';
	const ALGO_SHA256 = 'sha256';

	const LEVEL_IDENTICAL = -3;
	const LEVEL_EXACT = -2;
	const LEVEL_SIMILAR = -1;
	const LEVEL_AT_AROUND = 0;

}
