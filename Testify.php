<?php

class Testify
{
	public $template = 'testify.report.php';
	protected $tests = array();
	protected $stack = array();
	protected $fileCache = array();
	protected $currentTestCase;
	protected $suiteTitle;
	protected $suiteResults;

	protected $before = NULL;
	protected $after = NULL;
	protected $beforeEach = NULL;
	protected $afterEach = NULL;

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
		$this->data = new stdClass;
		$this->suiteResults = array('pass' => 0, 'fail' => 0);
	}

	/**
	 * This is where all the magic happens. This handles individual tests as
	 * well as the assertions that will be made inside the test.
	 *
	 * $this->theTestNameHere(function(){..});
	 *
	 * $this->isAFoo('foo' == 'foo');
	 *
	 * @param string $name The test name
	 * @param array $args The method arguments
	 */
	public function __call($name, $args)
	{
		if(strpos($name, 'is') === 0)
		{
			$this->recordTest($args[0], $this->unCamelCase(substr($name, 2)));
			return $this;
		}

		$this->tests[] = array(
			"name" => $this->unCamelCase($name),
			"test" => $args[0]
		);

		return $this;
	}

	/**
	 * Convert underscore and camelCase names to sentences
	 *
	 * @param string $text The_string_toConvertTo_plainText
	 * @return string
	 */
	protected function unCamelCase($text)
	{
		return ltrim(ucwords(preg_replace('/[A-Z]+/', ' $0', str_replace('_', ' ', $text))));
	}

	/**
	 * Executed once before the test cases are run.
	 *
	 * @param function $callback An anonymous callback function
	 */
	public function before($callback)
	{
		$this->before = $callback;
	}

	/**
	 * Executed once after the test cases are run.
	 *
	 * @param function $callback An anonymous callback function
	 */
	public function after($callback)
	{
		$this->after = $callback;
	}

	/**
	 * Executed for every test case, before it is run.
	 *
	 * @param function $callback An anonymous callback function
	 */
	public function beforeEach($callback)
	{
		$this->beforeEach = $callback;
	}

	/**
	 * Executed for every test case, after it is run.
	 *
	 * @param function $callback An anonymous callback function
	 */
	public function afterEach($callback)
	{
		$this->afterEach = $callback;
	}

	/**
	 * Run all the tests and before / after functions. Calls {@see report} to generate the HTML report page.
	 *
	 * @return $this
	 */
	public function run()
	{
		if(is_callable($this->before))
		{
			call_user_func($this->before, $this);
		}

		foreach($this->tests as $test)
		{
			$this->currentTestCase = $test['name'];

			try {

				if(is_callable($this->beforeEach))
				{
					call_user_func($this->beforeEach, $this);
				}

				call_user_func($test['test'], $this);

				if(is_callable($this->afterEach))
				{
					call_user_func($this->afterEach, $this);
				}

			} catch (Exception $e) {
				$this->fail(false, $e->getMessage());
			}
		}

		if(is_callable($this->after))
		{
			call_user_func($this->after, $this);
		}

		return $this;
	}

	/**
	 * Unconditional pass
	 *
	 * @param boolean $value Boolean TRUE/FALSE
	 * @param string $name The name of the pass
	 */
	public function pass($value = true, $name = 'Pass')
	{
		return $this->recordTest($value, $name);
	}

	/**
	 * Unconditional fail
	 *
	 * @param boolean $value Boolean TRUE/FALSE
	 * @param string $name The name of the fail
	 */
	public function fail($value = false, $name = 'Fail')
	{
		return $this->recordTest($value, $name);
	}

	/**
	 * Generates a pretty HTML5 report of the test suite status. Called implicitly by {@see run}
	 *
	 * @return $this
	 */
	public function HTMLreport()
	{
		$title = $this->suiteTitle;
		$suiteResults = $this->suiteResults;
		$cases = $this->stack;

		include $this->template;
	}

	/**
	 * Send a report to the command line
	 */
	public function cliReport()
	{
		$line = str_repeat('-', 80) . "\n";

		foreach($this->stack as $title => $case)
		{
			$title = $this->colorCLI($title, ($case['fail'] ? 'red' : 'green'));

			print ($case['fail'] ? 'FAIL' : 'PASS') . ': ' . $title . "\n" . $line;

			foreach ($case['tests'] as $test)
			{
				$color = $test['result'] == 'pass' ? 'green' : 'red';

				print $this->colorCLI($test['type'], $color) .  "\n";
				print $test['file'] . '['. $test['line'] . ']: ' . $test['source'] . "\n";
			}

			print "\n\n";
		}

		print 'SUITE: '. $this->colorCLI($this->suiteTitle, 'blue', TRUE) . "\n" . $line;
		print "Tests Failed: ". $this->colorCLI($this->suiteResults['fail'], 'red'). "\n";
		print "Tests Passed: ". $this->colorCLI($this->suiteResults['pass'], 'green'). "\n\n";
	}

	/**
	 * Color output text for the CLI
	 *
	 * @param string $text to color
	 * @param string $color of text
	 * @param string $background color
	 */
	public function colorCLI($text, $color, $bold = FALSE)
	{
		// Standard CLI colors
		$colors = array_flip(array(30 => 'gray', 'red', 'green', 'yellow', 'blue', 'purple', 'cyan', 'white', 'black'));

		// Escape string with color information
		return"\033[" . ($bold ? '1' : '0') . ';' . $colors[$color] . "m$text\033[0m";
	}

	/**
	 * A helper method for recording the results of the assertions in the internal stack.
	 *
	 * @param boolean $pass If equals true, the test has passed, otherwise failed.
	 * @param string $name The name for this test
	 * @return boolean
	 */
	protected function recordTest($pass, $name)
	{
		if(empty($this->stack[$this->currentTestCase]))
		{
			$this->stack[$this->currentTestCase]['tests'] = array();
			$this->stack[$this->currentTestCase]['pass'] = 0;
			$this->stack[$this->currentTestCase]['fail'] = 0;
		}

		$bt = debug_backtrace();

		// We need to go back an extra level for caught exceptions
		$i = basename($bt[1]['file'], '.php') == __CLASS__ ? 2 : 1;
		$source = $this->getFileLine($bt[$i]['file'], $bt[$i]['line'] - 1);
		$bt[$i]['file'] = basename($bt[$i]['file']);

		$result = $pass ? "pass" : "fail";
		$this->stack[$this->currentTestCase]['tests'][] = array(
			"type"		=> $name ? $name : 'True',
			"result"	=> $result,
			"line"		=> $bt[$i]['line'],
			"file"		=> $bt[$i]['file'],
			"source"	=> $source
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
	protected function getFileLine($file, $line)
	{
		if( ! array_key_exists($file, $this->fileCache))
		{
			$this->fileCache[$file] = file($file);
		}

		return trim($this->fileCache[$file][$line]);
	}

}

/**
 * TestifyException class
 */
class TestifyException extends Exception {}
