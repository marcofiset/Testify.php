<?php

/**
 * Testify class
 *
 * Testify.php - a micro unit testing framework
 *
 * @package    Testify
 * @link       http://tutorialzine.com/testify/
 * @license    GPL
 */
class Testify
{
	/**
	 * A template file path
	 *
	 * @var    string
	 */
	private $template;

	/**
	 * An array of test closures
	 *
	 * @var    array
	 */
	private $tests = array();

	/**
	 * The unit test results
	 *
	 * @var    array
	 */
	private $stack = array();

	/**
	 * Cached files for {@see getFileLine}
	 *
	 * @var    array
	 */
	private $fileCache = array();

	/**
	 * The name of the current test
	 *
	 * @var    string
	 */
	private $currentTestCase;

	/**
	 * Name of the test suite
	 *
	 * @var    string
	 */
	private $suiteTitle;

	/**
	 * The total results of the test suite
	 *
	 * @var    array
	 * @var type
	 */
	private $suiteResults = array(
		'pass' => 0,
		'fail' => 0,
	);

	/**
	 * A closure, called once at the beginning of the test suite
	 *
	 * @var    Closure
	 */
	private $before;

	/**
	 * A closure, called once at the end of the test suite
	 *
	 * @var    Closure
	 */
	private $after;

	/**
	 * A closure, called once before each test case
	 *
	 * @var    Closure
	 */
	private $beforeEach;

	/**
	 * A closure, called once after each test case
	 *
	 * @var    Closure
	 */
	private $afterEach;

	/**
	 * A public object for storing state and other variables across test cases and method calls.
	 *
	 * @var stdClass
	 */
	public $data;

	/**
	 * Default constructor
	 *
	 * @param    string           The name of the test suite
	 * @param    string|null      Optionally, an absolute template file path
	 * @return   void             No value is returned
	 */
	public function __construct($title, $template = null)
	{
		$this->suiteTitle = $title;
		$this->data       = new stdClass;

		if($template === null)
		{
			$template = __DIR__.'/../views/report.php';
		}

		$this->template = realpath($template);
	}

	/**
	 * This is where all the magic happens. This handles individual tests as
	 * well as the assertions that will be made inside the test.
	 *
	 * $this->theTestNameHere(function(){..});
	 * $this->isAFoo('foo' == 'foo');
	 *
	 * @param    string           The method (test) name
	 * @param    array            An array of arguments
	 * @return   self
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
	 * @param    string           The_string_toConvertTo_plainText
	 * @return   string           The converted string
	 */
	private function unCamelCase($text)
	{
		return ltrim(ucwords(preg_replace('/[A-Z]+/', ' $0', str_replace('_', ' ', $text))));
	}

	/**
	 * Set the anonymous function to be called once before the test suite is
	 * started
	 *
	 * @param    Closure          An anonymous callback function
	 * @return   void             No value is returned
	 */
	public function before(Closure $callback)
	{
		$this->before = $callback;
	}

	/**
	 * Set the anonymous function to be called once after the test suite is
	 * finished
	 *
	 * @param    Closure          An anonymous callback function
	 * @return   void             No value is returned
	 */
	public function after(Closure $callback)
	{
		$this->after = $callback;
	}

	/**
	 * Set the anonymous function to be called once before every test case is
	 * run
	 *
	 * @param    Closure          An anonymous callback function
	 * @return   void             No value is returned
	 */
	public function beforeEach($callback)
	{
		$this->beforeEach = $callback;
	}

	/**
	 * Set the anonymous function to be called once after every test case is run
	 *
	 * @param    Closure          An anonymous callback function
	 * @return   void             No value is returned
	 */
	public function afterEach($callback)
	{
		$this->afterEach = $callback;
	}

	/**
	 * Run all of the tests and before/after functions. Call {@see report} to
	 * generate the HTML report page
	 *
	 * @return   self
	 */
	public function run()
	{
		if($this->before !== null)
		{
			$this->before($this);
		}

		foreach($this->tests as $test)
		{
			$this->currentTestCase = $test['name'];

			try
			{
				if($this->beforeEach !== null)
				{
					$this->beforeEach($this);
				}

				$test['test']($this);

				if($this->afterEach !== null)
				{
					$this->afterEach($this);
				}
			}
			catch(Exception $e)
			{
				$this->fail(false, $e->getMessage());
			}
		}

		if($this->after !== null)
		{
			$this->after($this);
		}

		$this->report();
		return $this;
	}

	/**
	 * Unconditional pass
	 *
	 * @param    boolean          Boolean true/false
	 * @param    string           Name of the unconditonal pass
	 * @return   boolean          True if passed, otherwise false
	 */
	public function pass($value = true, $name = 'Pass')
	{
		return $this->recordTest((bool) $value, (string) $name);
	}

	/**
	 * Unconditional fail
	 *
	 * @parm     boolean          Boolean true/false
	 * @param    string           Name of the unconditional pass
	 * @return   boolean          True if passed, otherwise false
	 */
	public function fail($value = false, $name = 'Fail')
	{
		return $this->recordTest((bool) $value, (string) $name);
	}

	/**
	 * Gateway report method. This will call {@see reportCli} or
	 * {@see reportHtml} depending on the environment
	 *
	 * @return   self
	 */
	public function report()
	{
		if(PHP_SAPI == 'cli' or defined('STDOUT'))
		{
			return $this->reportCli();
		}

		return $this->reportHtml();
	}

	/**
	 * Generates a pretty HTML5 report of the test suite status. Called
	 * by {@see report} when in an HTTP context
	 *
	 * @return   self
	 */
	private function reportHtml()
	{
		// Bring variables down to a local scope
		$title        = $this->suiteTitle;
		$suiteResults = $this->suiteResults;
		$cases        = $this->stack;

		include $this->template;

		return $this;
	}

	/**
	 * Generates a command line report of the test suite status. Called by
	 * {@see report} when in a command-line context
	 *
	 * @return   self
	 */
	private function reportCli()
	{
		$line = str_repeat('-', 80)."\n";

		foreach($this->stack as $title => $case)
		{
			$title = $this->colorCLI($title, ($case['fail'] ? 'red' : 'green'));

			print ($case['fail'] ? 'FAIL' : 'PASS').': '.$title."\n".$line;

			foreach($case['tests'] as $test)
			{
				$color = $test['result'] == 'pass' ? 'green' : 'red';

				print $this->colorCLI($test['type'], $color)."\n";
				print $test['file'].'['.$test['line'].']: '.$test['source']."\n";
			}

			print "\n\n";
		}

		print 'SUITE: '.$this->colorCLI($this->suiteTitle, 'blue', TRUE)."\n".$line;
		print "Tests Failed: ".$this->colorCLI($this->suiteResults['fail'], 'red')."\n";
		print "Tests Passed: ".$this->colorCLI($this->suiteResults['pass'], 'green')."\n\n";

		return $this;
	}

	/**
	 * A helper method for coloring text in CLI
	 *
	 * @param    string           The text to color
	 * @param    string           The color to change the text
	 * @param    boolean          Whether to bold the text or not
	 * @return   string           The colored string
	 */
	private function colorCLI($text, $color, $bold = FALSE)
	{
		// Standard CLI colors
		$colors = array_flip(array(
			30 => 'gray',
			'red',
			'green',
			'yellow',
			'blue',
			'purple',
			'cyan',
			'white',
			'black')
		);

		// Escape string with color information
		return"\033[".($bold ? '1' : '0').';'.$colors[$color]."m$text\033[0m";
	}

	/**
	 * A helper method for recording the results of assertions to the internal
	 * stack
	 *
	 * @param    boolean          True if passed, otherwise false
	 * @param    string           The name of the test
	 * @return   boolean          Returns true if passed, otherwise false
	 */
	private function recordTest($pass, $name)
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
			"type"   => $name ? $name : 'True',
			"result" => $result,
			"line"   => $bt[$i]['line'],
			"file"   => $bt[$i]['file'],
			"source" => $source
		);

		$this->stack[$this->currentTestCase][$result]++;
		$this->suiteResults[$result]++;

		return $pass;
	}

	/**
	 * A helper method for fetching a specific line of a file, with caching.
	 *
	 * @param    string           The file name
	 * @param    integer          The line number to retrieve
	 * @return   string           The line within the file
	 */
	private function getFileLine($file, $line)
	{
		if(!isset($this->fileCache[$file]))
		{
			$this->fileCache[$file] = file($file);
		}

		return trim($this->fileCache[$file][$line]);
	}
}