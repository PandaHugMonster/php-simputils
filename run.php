<?php

use spaf\simputils\components\SystemFingerprint;
use spaf\simputils\helpers\SystemHelper;
use function spaf\simputils\basic\pd;

require_once 'vendor/autoload.php';

$config = PHP::init();

$file = fl($config->working_dir.'/.env');

pd($file);
