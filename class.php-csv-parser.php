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
		 * The array to hold the final parsed and cleaned up content
		 * @var array
		 */
		private $parsed_data_array;
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
		 * The container for all monitored events and their respective handlers
		 * @var array
		 */
		private $event_handlers = array();
		/**
		 * The class constructor
		 */
		public function __construct(){
			$this->data_source = null;
			$this->data_array = array();
			$this->parsed_data_array = array();
			$this->options_default = array(
				"comment"=>"#",		#Treats all the characteres after this one as a comment, default to ‘#’
				"delimiter"=>",",	#Set the field delimiter. One character only, defaults to comma.
				"escape"=>'"',		#Set the escape character, one character only, defaults to double quotes. **NOT USED YET**
				"quote"=>'"',	#Optional character surrounding a field, one character only, defaults to double quotes
				"rowDelimiter"=>"auto"	#String used to delimit record rows or a special value; Applicable to from_string() only
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
			$exception = new PhpCSV_Parser_Exception($message,__FILE__,$linenum,$description);
			if($this->hasHandler(self::EVENT_ON_ERROR)){
				$this->fire(self::EVENT_ON_ERROR,array($exception));
			} else {
				throw $exception;
			}
		}
		/**
		 * Sets the options on the parser
		 * @param array $options The options array support certain settings which are highlighted below
		 *		<ul>
		 *			<li><b>comment</b>: a single character that is used to identify lines containing comments (default: #)</li>
		 *			<li><b>delimiter</b>: a single character that serves as the delimiter for content on each line (default: ,)</li>
		 *			<li><b>escape</b>: a single character that's used to enclose each field value (default: ")</li>
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
			$row_delimiter = (strtolower($this->options['rowDelimiter']) === "auto")? PHP_EOL:$this->options['rowDelimiter'];
			$lines = explode($row_delimiter, $csv_string);
			$this->setDataArray($lines);
			$this->parse();
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
			$this->parse();
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
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			$lines = @file($csv_filepath, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
			if($lines === false){
				$this->throwException(
					"Failed to gain access to the file",
					$error_line,
					"It could be that the path doesn't exist or the current permission settings prevent PHP from being able
						to access the file!!"
				);
			}
			$this->setDataArray($lines);
			$this->parse();
			return $this;
		}
		/**
		 * Takes an open file handle and uses the referenced resource as the data source for the parser
		 * @param resource $file_handle A resource obtained by using fopen() in read mode
		 * @param array $options
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		public function from_stream(&$file_handle, array $options=null){
			$this->setOptions($options);
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			if($file_handle === false){
				$this->throwException(
					"Invalid file resource handler",
					$error_line,
					"It could be that the path doesn't exist or the current permission settings prevent PHP from being able
						to access the file!!"
				);
			}
			$lines = array();
			rewind($file_handle);
			while(!feof($file_handle)){
				$lines[] = fgets($file_handle);
			}
			$error_line = __LINE__ + 1;
			if(empty($lines)){
				$this->throwException(
					"Empty CSV file",
					$error_line,
					"No content could be read from the file pointed to by the resource stream"
				);
			}
			$this->setDataArray($lines);
			$this->parse();
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
		/**
		 * Registers a new handler for the specified event
		 * @param string $event_name The name of the event to register the callable for. One of the EVENT_ON_* constants
		 * @param callable $callable The callable to be notified when the event occurs
		 * @return \PhpCSV\PhpCSV_Parser#
		 */
		public function on($event_name, $callable){
			$this->event_handlers[$event_name] = $callable;
			return $this;
		}
		/**
		 * Checks if the specified event handler exists
		 * @param string $event_name
		 * @return bool
		 */
		private function hasHandler($event_name){
			return array_key_exists($event_name, $this->event_handlers);
		}
		/**
		 * Fires (or Initiates) an event and thus executes the attached callable if it exists
		 * @param string $event_name One of the EVENT_ON_* constants
		 * @param array $event_data
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		public function fire($event_name, array $event_data){
			if($this->hasHandler($event_name) and is_callable($this->event_handlers[$event_name])){
				#a handler exists for the event
				$handler = $this->event_handlers[$event_name];
				switch($event_name){
					case self::EVENT_ON_CLOSE:
						break;
					case self::EVENT_ON_DATA:
						break;
					case self::EVENT_ON_END:
						break;
					case self::EVENT_ON_ERROR:
						call_user_func_array($handler,$event_data);
						break;
					case self::EVENT_ON_RECORD:
						break;
				}
			}
			return $this;
		}
		private function parse(){

		}
		public function to_array(){

		}
		public function to_file(){

		}
	}
?>