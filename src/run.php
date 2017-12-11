<?php

define('SRC_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

require_once(SRC_ROOT . 'LwacParser.php');
require_once(SRC_ROOT . 'LwacExtractor.php');
require_once(SRC_ROOT . 'CourtNames.class.php');
require_once(SRC_ROOT . 'HearingDate.class.php');

//$class = new LwacParser(__DIR__ . '/../docs/');
$class = new LwacParser(dirname(dirname(__FILE__)).'/docs/');
$output = $class->run();

