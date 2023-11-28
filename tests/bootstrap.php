<?php

use Kirby\Cms\App;

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');


require_once __DIR__ . '/../vendor/autoload.php';

define('KIRBY_HELPER_DUMP', false);
define('KIRBY_TESTING', true);

// disable Whoops for all tests that don't need it
// to reduce the impact of memory leaks
App::$enableWhoops = false;

$app = new App([
	'options' => [
		'debug' => true,
	],
]);
