<?php

use spaf\simputils\components\SystemFingerprint;
use spaf\simputils\helpers\SystemHelper;
use function spaf\simputils\basic\pd;

require_once 'vendor/autoload.php';



$sf = new SystemFingerprint();
echo $sf;
pd();

/** @var \spaf\simputils\components\SystemFingerprint $sfp */
$sfp = SystemHelper::systemFingerprint('2.0.0');

$version1 = 'DSF/9ad2f41fc1e44450714bad01764ff7d9,' .
	'4b01641228e44a8be26d0071c3f7bb0e94800217753d5034c4b00986cd381f22/0.2.5/0';
pd(strval($sfp), $sfp->fit($version1)?'true':'false');
