<?php

/*
 * A more complex example using beforeEach and data
 * 
 */

require '../testify/testify.class.php';

$tf = new Testify("A bit more advanced test suite");

// Before each is called before every test case

$tf->beforeEach(function($tf){
	
	// Use $tf like an array to share variables across tests
	
	$tf["arr"] = array('a','b','c','d','e','f');
	
});

$tf->test("Testing Array Pop", function($tf){

	$arr = $tf["arr"];
	
	$tf->assertEqual(array_pop($arr),'f');
	$tf->assertEqual(array_pop($arr),'e');
	$tf->assertEqual(array_pop($arr),'d');
	$tf->assertEqual(array_pop($arr),'c');

});

$tf->test("Testing In Array", function($tf){

	// beforeEach has restored the array

	$arr = $tf["arr"];
	
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

?>