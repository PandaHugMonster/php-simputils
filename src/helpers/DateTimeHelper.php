<?php


namespace spaf\simputils\helpers;


use spaf\simputils\interfaces\helpers\DateTimeHelperInterface;
use spaf\simputils\traits\helpers\DateTimeTrait;

class DateTimeHelper implements DateTimeHelperInterface {
	public static ?string $now_string = null;
	use DateTimeTrait;
}