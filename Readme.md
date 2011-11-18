Testify.php - a micro unit testing framework
============================================

Testify makes writing unit tests fun again. It has an elegant syntax and keeps things simple.

Here is an example for a test suite with two test cases:

```php
include "testify/testify.class.php";
include "MyCalc.php";

$tf = new Testify("MyCalc Test Suite");

$tf->beforeEach(function($tf){
	$tf->data->calc = new MyCalc(10);
});

$tf->test("Testing the add() method", function($tf){
	$calc = $tf->data->calc;
	
	$calc->add(4);
	$tf->assert($calc->result() == 14);

	$calc->add(-5);
	$tf->assertEqual($calc->result(),9);
});

$tf->test("Testing the mul() method", function($tf){
	$calc = $tf->data->calc;
	
	$calc->mul(1.5);
	$tf->assertEqual($calc->result(),15);

	$calc->mul(-1);
	$tf->assertEqual($calc->result(),-15);
});

$tf->run();
```

For full documentation and a getting started guide, visit the [Testify homepage](http://tutorialzine.com/projects/testify/). 