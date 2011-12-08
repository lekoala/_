<?php
/* @link http://tutorialzine.com/projects/testify/ */

include('testify.class.php');
include('../_.php');

$tf = new Testify('Underscore test suite');

$tf->test('init', function($tf) {
	_::init('Europe/Berlin', false,false);
	$tf->assert(date_default_timezone_get() == 'Europe/Berlin');
	$tf->assert(mb_internal_encoding() == 'ISO-8859-1');
	$tf->assert(error_reporting() == 0);
	$tf->assert(ini_get('display_errors') == 0);
	_::init();
	$tf->assert(date_default_timezone_get() == 'Europe/Brussels');
	$tf->assert(mb_internal_encoding() == 'UTF-8');
	$tf->assert(error_reporting() == E_ALL | E_STRICT);
	$tf->assert(ini_get('display_errors') == 1);
});
$tf->test('render', function($tf) {
	
});

$tf->run();
