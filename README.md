# Testify.php - a micro unit testing framework

Testify makes writing unit tests fun again. It has an elegant syntax and keeps
things simple.

You can use camelCase or underscore_based test names and assertions. Write tests
like you're speaking!

Here is an example for a test suite with two test cases:

```php
require 'Testify.php';

$tf = new Testify("A Basic Test Suite");

// Add a test case
$tf->justTestingAround(function($tf)
{
	$tf->is(true);
	$tf->isFalse(false == false);
	$tf->isEqual(1 == '1');
	$tf->isIdentical(1 === 1);

	$tf->isInArray(in_array('a', array(1,2,3,4,5,'a')));
	$tf->isObject(new stdClass instanceof stdClass);
	$tf->pass();
});

$tf->iHaveA_BadFeelingAboutThisOne(function($tf)
{
	$tf->is(false);
	$tf->isFalse(true == FALSE);
	$tf->isEqual(1 == '-21');
	$tf->isIdentical(1 === '1');

	$tf->isInArray(in_array('b',array(1,2,3,4,5,'a')));
	$tf->fail();
});

$tf->run();
```

For full documentation and a getting started guide, visit the
[Testify homepage](http://tutorialzine.com/projects/testify/).