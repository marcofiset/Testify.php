<?php

require 'vendor/autoload.php';

use Testify\Testify;

$tf = new Testify("My test suite");

// add a test case
$tf->test("Some tests", function($tf)
{
	$tf->assert(true);
	$tf->assertFalse(!true);
	$tf->assertEqual(1337, '1337');
	$tf->assertIdentical(1024, pow(2, 10));
});

$tf(); // run all tests
