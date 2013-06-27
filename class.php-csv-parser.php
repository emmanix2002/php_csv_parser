<?php
	namespace PhpCSV;
	$cwd = dirname(__FILE__);
	require_once($cwd."/class.php-csv-parser-exception.php");
	/**
	 * This class is a simple PHP CSV parser inspired by the Nodejs CSV project (see https://github.com/wdavidw/node-csv).
	 * It presents a lot of the functionality (with some minor changes) in the NodeJs project for use in everyday CSV
	 * processing in PHP.
	 * Hope someone finds it useful
	 *
	 * @author Okeke Emmanuel <emmanix2002@gmail.com>
	 * @version 1.0.0
	 * @package Emmanuel/php_csv_parser/
	 * @link
	 */
	class PhpCSV_Parser {
		/**
		 * The array to hold the raw data before it's processed
		 * @var array
		 */
		private $data_array;
		/**
		 * The array to hold the options for the parser
		 * @var array
		 */
		private $options;
		/**
		 * The array to hold the default options for the parser
		 * @var array
		 */
		private $options_default;

		const EVENT_ON_RECORD = "record";
		const EVENT_ON_DATA = "data";
		const EVENT_ON_CLOSE = "close";
		const EVENT_ON_END = "end";
		const EVENT_ON_ERROR = "error";
		/**
		 * The class constructor
		 */
		public function __construct(){
			$this->data_source = null;
			$this->data_array = array();
			$this->options_default = array(
				"comment"=>"#",
				"delimiter"=>","
			);
		}
		/**
		 * Handles the function of throwing an exception for this file
		 * @param string $message
		 * @param int $linenum
		 * @param string $description
		 * @throws PhpCSV_Parser_Exception
		 */
		private function throwException($message, $linenum, $description){
			throw new PhpCSV_Parser_Exception($message,__FILE__,$linenum,$description);
		}
		/**
		 * Sets the options on the parser
		 * @param array $options The options array support certain settings which are highlighted below
		 *		<ul>
		 *			<li><b>comment</b>: a single character that is used to identify lines containing comments (default: #)</li>
		 *			<li><b>delimiter</b>: a single character that serves as the delimiter for content on each line (default: ,)</li>
		 *		</ul>
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		public function setOptions(array $options=null){
			$this->options = array_merge($this->options_default,$options);
			return $this;
		}
		/**
		 * Sets the CSV data to be processed as a string
		 * @param string $csv_string
		 * @param array $options The options array to use for the parsing (see the setOption() method)
		 * @return \PhpCSV\PhpCSV_Parser
		 * @see setOptions()
		 * @throws PhpCSV_Parser_Exception
		 */
		public function from_string($csv_string=null, array $options=null){
			$this->setOptions($options);
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			if($csv_string === null or !is_string($csv_string) or strlen($csv_string) === 0){
				$this->throwException(
					"Argument 1 should be a non-empty string",
					$error_line,
					"Arguments 1 must be a string"
				);
			}
			$lines = explode(PHP_EOL, $csv_string);
			$this->setDataArray($lines);
			return $this;
		}
		/**
		 * Takes an array of strings and sets it up for parsing
		 * @param array $array The array of strings to be processed
		 * @param array $options
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		public function from_array(array $array, array $options=null){
			$this->setOptions($options);
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			if(empty($array)){
				$this->throwException(
					"Argument 1 should be a non-empty array",
					$error_line,
					"Arguments 1 must be an array that contains at least one value"
				);
			}
			$this->setDataArray($array);
			return $this;
		}
		/**
		 * Takes a filepath to a CSV file and uses it as the input source for the parser
		 * @param string $csv_filepath
		 * @param array $options
		 * @return \PhpCSV\PhpCSV_Parser
		 * @throws PhpCSV_Parser_Exception
		 */
		public function from_path($csv_filepath=null, array $options=null){
			$this->setOptions($options);
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			if($csv_filepath === null or !is_string($csv_filepath) or strlen($csv_filepath) === 0){
				$this->throwException(
					"Argument 1 should be a non-empty string",
					$error_line,
					"Arguments 1 must be a string"
				);
			}
			$file_handle = @fopen($csv_filepath, "rt");
			return $this;
		}

		public function from_stream(&$file_handle, array $options=null){
			$this->setOptions($options);
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			return $this;
		}
		/**
		 * Sets the data array with the content that will be processed at a later time
		 * @param array $lines An array containing the raw processed data
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		private function setDataArray(array $lines){
			foreach($lines as $line){
				if(substr($line, 0, 1) !== $this->options['comment']){
					#not a comment line
					$this->data_array[] = $line;
				}
			}
			return $this;
		}

		public function on($event_name, $callable){

		}

	}
?>