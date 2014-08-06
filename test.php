<?php

require 'vendor/autoload.php';

use Testify\Testify;

$tf = new Testify("My test suite");

// add a test case
$tf->test("Some tests", function($tf)
{
    $tf->assert(true);
    $tf->assertFalse(!true);
    $tf->assertEquals(1337, '1337');
	$tf->assertNotEquals(array('a', 'b', 'c'), array('a', 'c', 'd'), "Not the same order");
    $tf->assertEquals(new stdClass, new stdClass, "Classes are equals");
});

$tf->test(function($tf)
{
    $tf->assert(true, "Always true !");
    $tf->assertSame(1024, pow(2, 10));
    $tf->assertNotSame(new stdClass, new stdClass, "Not the same classes !");
});

$tf(); // run all tests
