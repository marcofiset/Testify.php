<?php

namespace Testify;

/**
 * Testify.php - a micro unit testing framework
 *
 * This is the main class of the framework. Use it like this:
 *
 * @version    0.3.1
 * @author     Martin Angelov
 * @author     Marc-Olivier Fiset
 * @link       marco
 * @throws     TestifyException
 * @license    GPL
 */

class Testify
{
    private $tests = array();
    private $stack = array();
    private $fileCache = array();
    private $currentTestCase;
    private $suiteTitle;
    private $suiteResults;

    private $before = NULL;
    private $after = NULL;
    private $beforeEach = NULL;
    private $afterEach = NULL;

    /**
     * A public object for storing state and other variables across test cases and method calls.
     *
     * @var stdClass
     */
    public $data = NULL;

    /**
     * The constructor
     *
     * @param string $title The suite title
     */
    public function __construct($title)
    {
        $this->suiteTitle = $title;
        $this->data = new \stdClass;
        $this->suiteResults = array('pass' => 0, 'fail' => 0);
    }

    /**
     * Add a test case.
     *
     * @param string $name Title of the test case
     * @param function $testCase The test case as an anonymous function
     * @return $this
     */
    public function test($name, $testCase = NULL)
    {
        if (is_callable($name)) {
            $testCase = $name;
            $name = "Test Case #" . (count($this->tests) + 1);
        }

        $this->affirmCallable($testCase,"test");

        $this->tests[] = array("name" => $name,"testCase" => $testCase);
        return $this;
    }

    /**
     * Executed once before the test cases are run.
     *
     * @param function $callback An anonymous callback function
     */
    public function before($callback)
    {
        $this->affirmCallable($callback,"before");
        $this->before = $callback;
    }

    /**
     * Executed once after the test cases are run.
     *
     * @param function $callback An anonymous callback function
     */
    public function after($callback)
    {
        $this->affirmCallable($callback,"after");
        $this->after = $callback;
    }

    /**
     * Executed for every test case, before it is run.
     *
     * @param function $callback An anonymous callback function
     */
    public function beforeEach($callback)
    {
        $this->affirmCallable($callback,"beforeEach");
        $this->beforeEach = $callback;
    }

    /**
     * Executed for every test case, after it is run.
     *
     * @param function $callback An anonymous callback function
     */
    public function afterEach($callback)
    {
        $this->affirmCallable($callback,"afterEach");
        $this->afterEach = $callback;
    }

    /**
     * Run all the tests and before / after functions. Calls {@see report} to generate the HTML report page.
     *
     * @return $this
     */
    public function run()
    {
        $arr = array($this);

        if (is_callable($this->before)) {
            call_user_func_array($this->before, $arr);
        }

        foreach($this->tests as $test) {
            $this->currentTestCase = $test['name'];

            if (is_callable($this->beforeEach)) {
                call_user_func_array($this->beforeEach, $arr);
            }

            // Executing the testcase
            call_user_func_array($test['testCase'], $arr);

            if (is_callable($this->afterEach)) {
                call_user_func_array($this->afterEach, $arr);
            }
        }

        if (is_callable($this->after)) {
            call_user_func_array($this->after, $arr);
        }

        $this->report();

        return $this;
    }

    /**
     * Passes if given a truthfull expression
     *
     * @param boolean $arg The result of a boolean expression.
     * @return boolean
     */
    public function assert($arg, $test_name = '')
    {
        return $this->recordTest($arg == true, $test_name);
    }

    /**
     * Passes if given a falsy expression
     *
     * @param boolean $arg The result of a boolean expression.
     * @return boolean
     */
    public function assertFalse($arg, $test_name = '')
    {
        return $this->recordTest($arg == false, $test_name);
    }

    /**
     * Passes if $arg1 == $arg2
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @return boolean
     */
    public function assertEqual($arg1,$arg2, $test_name = '')
    {
        return $this->recordTest($arg1 == $arg2, $test_name);
    }

    /**
     * Passes if $arg1 === $arg2
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @return boolean
     */

    public function assertIdentical($arg1,$arg2, $test_name = '')
    {
        return $this->recordTest($arg1 === $arg2, $test_name);
    }

    /**
     * Passes if $arg is an element of $arr
     *
     * @param mixed $arg
     * @param array $arr
     * @return boolean
     */
    public function assertInArray($arg, Array $arr, $test_name = '')
    {
        return $this->recordTest( in_array($arg, $arr), $test_name);
    }

    /**
     * Passes if $arg is not an element of $arr
     *
     * @param mixed $arg
     * @param array $arr
     * @return boolean
     */
    public function assertNotInArray($arg, Array $arr, $test_name = '')
    {
        return $this->recordTest( !in_array($arg, $arr), $test_name);
    }

    /**
     * Unconditional pass
     *
     */
    public function pass()
    {
        return $this->recordTest(true);
    }

    /**
     * Unconditional fail
     *
     */
    public function fail()
    {
        // This check fails every time
        return $this->recordTest(false);
    }

    /**
     * Generates a pretty CLI or HTML5 report of the test suite status. Called implicitly by {@see run}
     *
     * @return $this
     */
    public function report()
    {
        $title = $this->suiteTitle;
        $suiteResults = $this->suiteResults;
        $cases = $this->stack;

        if (php_sapi_name() === 'cli') {
            include dirname(__FILE__).'/testify.report.cli.php';
        } else {
            include dirname(__FILE__).'/testify.report.php';
        }

        return $this;
    }

    /**
     * A helper method for recording the results of the assertions in the internal stack.
     *
     * @param boolean $pass If equals true, the test has passed, otherwise failed.
     * @return boolean
     */
    private function recordTest($pass, $test_name = '')
    {
        if (!array_key_exists($this->currentTestCase, $this->stack) ||
              !is_array($this->stack[$this->currentTestCase])) {

            $this->stack[$this->currentTestCase]['tests'] = array();
            $this->stack[$this->currentTestCase]['pass'] = 0;
            $this->stack[$this->currentTestCase]['fail'] = 0;
        }

        $bt = debug_backtrace();
        $source = $this->getFileLine($bt[1]['file'],$bt[1]['line'] - 1);
        $bt[1]['file'] = basename($bt[1]['file']);

        $result = $pass ? "pass" : "fail";
        $this->stack[$this->currentTestCase]['tests'][] = array(
            "name"      => $test_name,
            "type"      => $bt[1]['function'],
            "result"    => $result,
            "line"      => $bt[1]['line'],
            "file"      => $bt[1]['file'],
            "source"    => $source
        );

        $this->stack[$this->currentTestCase][$result]++;
        $this->suiteResults[$result]++;

        return $pass;
    }

    /**
     * Internal method for fetching a specific line of a text file. With caching.
     *
     * @param string $file The file name
     * @param number $line The line number to return
     * @return string
     */
    private function getFileLine($file, $line)
    {
        if (!array_key_exists($file,$this->fileCache)) {
            $this->fileCache[$file] = file($file);
        }

        return trim($this->fileCache[$file][$line]);
    }

    /**
     * Internal helper method for determine whether a variable is callable as a function.
     *
     * @param mixed $func The variable to check
     * @param string $name Used for the error message text to indicate the name of the parent context.
     */
    private function affirmCallable(&$func,$name)
    {
        if (!is_callable($func)) {
            throw new TestifyException("$name(): Please pass a valid callback function!");
        }
    }

    /**
     * Alias for "run()" method
     *
     * @see Testify::run()
     * @return $this
     */
    public function __invoke()
    {
        return $this->run();
    }
}

/**
 * TestifyException class
 *
 */
class Exception extends \Exception
{

}
