<?php

/*
 * A more complex example using beforeEach and data
 *
 */

require '../vendor/autoload.php';

use Testify\Testify;

$tf = new Testify("A bit more advanced test suite");

// Before each is called before every test case

$tf->beforeEach(function($tf) {

	// Use the data property to share variables across tests

	$tf->data->arr = array('a','b','c','d','e','f');

});

$tf->test("Testing Array Pop", function($tf) {

	$arr = &$tf->data->arr;

	$tf->assertEqual(array_pop($arr),'f');
	$tf->assertEqual(array_pop($arr),'e');
	$tf->assertEqual(array_pop($arr),'d');
	$tf->assertEqual(array_pop($arr),'c');

});

$tf->test("Testing In Array", function($tf) {

	// beforeEach has restored the array

	$arr = &$tf->data->arr;

	$tf->assertInArray('a',$arr);
	$tf->assertInArray('b',$arr);
	$tf->assertInArray('c',$arr);
	$tf->assertInArray('d',$arr);
	$tf->assertInArray('e',$arr);
	$tf->assertInArray('f',$arr);
	$tf->assertNotInArray('g',$arr);
});

# It is even possible to include additional files with tests:
include 'subtest.php';

$tf->run();
