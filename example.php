<?php

/*
 * This is a minimal example of Testify
 *
 */
require 'Testify.php';

$tf = new Testify("A Basic Test Suite");

// Add a test case
$tf->justTestingAround(function($tf){

	$tf->is(true);
	$tf->isFalse(false == false);
	$tf->isEqual(1 == '1');
	$tf->isIdentical(1 === 1);

	$tf->isInArray(in_array('a', array(1,2,3,4,5,'a')));
	$tf->isObject(new stdClass instanceof stdClass);
	$tf->pass();

});

$tf->iHaveA_BadFeelingAboutThisOne(function($tf){

	$tf->is(false);
	$tf->isFalse(true == FALSE);
	$tf->isEqual(1 == '-21');
	$tf->isIdentical(1 === '1');

	$tf->isInArray(in_array('b',array(1,2,3,4,5,'a')));
	$tf->fail();

});

$tf->methodChaining_test_withException(function($tf)
{
	$tf->isStillUp($stillUp = TRUE)
		->is_ok(TRUE)
		->isBorn_inTheUSA('USA' == 'UK');

	throw new Exception('This is unexpected!');
});


$tf->run();

if(PHP_SAPI == 'cli')
	$tf->CLIReport();
else
	$tf->HTMLReport();

