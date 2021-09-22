<?php


namespace spaf\simputils\interfaces;


interface LoggerInterface {

	const LEVEL_CRITICAL = 50;
	const LEVEL_ERROR = 40;
	const LEVEL_WARNING = 30;
	const LEVEL_INFO = 20;
	const LEVEL_DEBUG = 10;
	const LEVEL_NOT_SET = 0;

	const TEMPLATE_NAME = '%(name)s';
	const TEMPLATE_MESSAGE = '%(message)s';
	const TEMPLATE_LINE_NUMBER = '%(lineno)d';
	const TEMPLATE_LEVEL_NAME = '%(levelname)s';
	const TEMPLATE_LEVEL_NUMBER = '%(levelno)s';
	const TEMPLATE_HUMAN_TIME = '%(asctime)s';
	const TEMPLATE_CREATED_TIME = '%(created)f';
	const TEMPLATE_FUNCTION_NAME = '%(funcname)s';
	const TEMPLATE_FILE_NAME = '%(filename)s';

}