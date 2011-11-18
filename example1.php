<?php

/*
 * This is a minimal example of Testify
 * 
 */

require 'testify/testify.class.php';

$tf = new Testify("A basic test suite.");

// Add a test case
$tf->test("Just testing around", function($tf){

	$tf->assert(true);
	$tf->assertFalse(false);
	$tf->assertEqual(1,'1');
	$tf->assertIdentical(1,1);
	
	$tf->assertInArray('a',array(1,2,3,4,5,'a'));
	$tf->pass();

});

$tf->test("I've got a bad feeling about this one", function($tf){

	$tf->assert(false);
	$tf->assertFalse(true);
	$tf->assertEqual(1,'-21');
	$tf->assertIdentical(1,'1');
	
	$tf->assertInArray('b',array(1,2,3,4,5,'a'));
	$tf->fail();

});

$tf->run();

?>