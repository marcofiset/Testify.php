<?php

/*
 * This is a minimal example of Testify
 *
 */

require '../vendor/autoload.php';

use Testify\Testify;

$tf = new Testify("A basic test suite.");

// Add a test case
$tf->test("Just testing around", function($tf) {

	$tf->assert(true, "Must pass !");
	$tf->assertFalse(false);
	$tf->assertEquals(1,'1');
	$tf->assertSame(1,1);

	$tf->assertInArray('a',array(1,2,3,4,5,'a'));
	$tf->pass("Always pass");

});

$tf->test("I've got a bad feeling about this one", function($tf) {

	$tf->assert(false);
	$tf->assertFalse(true);
	$tf->assertEquals(1,'-21');
	$tf->assertSame(1,'1');

	$tf->assertInArray('b',array(1,2,3,4,5,'a'));
	$tf->fail();

});

$tf();
