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
		 * The array to hold the options for the writer
		 * @var array
		 */
		private $options_to;
		/**
		 * The array to hold the default options for the writer
		 * @var array
		 */
		private $options_to_default;
		/**
		 * The array to hold the default options for the parser
		 * @var array
		 */
		private $options_default;
		/**
		 * The record event -- fired while parsing each row of the CSV data
		 */
		const EVENT_ON_RECORD = "record";
		/**
		 * The data event -- fired on each line(row) once the data has been transformed and stringified
		 */
		const EVENT_ON_DATA = "data";
		/**
		 * The close event -- fired when using the to_stream() after the file written to has been closed
		 */
		const EVENT_ON_CLOSE = "close";
		/**
		 * The end event --  fired after the CSV file has been completely parsed
		 */
		const EVENT_ON_END = "end";
		/**
		 * The error event -- fired whenever an error occurs
		 */
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
			$this->data_array = array();
			$this->parsed_data_array = array();
			$this->options_default = array(
				"comment"=>"#",		#Treats all the characteres after this one as a comment, default to ‘#’
				"delimiter"=>",",	#Set the field delimiter. One character only, defaults to comma.
				"escape"=>'"',		#Set the escape character, one character only, defaults to double quotes. **NOT USED YET**
				"quote"=>'"',	#Optional character surrounding a field, one character only, defaults to double quotes
				"rowDelimiter"=>"auto"	#String used to delimit record rows or a special value; Applicable to from_string() only
			);
			$this->options_to_default = array(
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
		 *			<li><b>escape</b>: a single character that's used to escape each field value (default: ")</li>
		 *			<li><b>quote</b>: a single character that's used to enclose each field value (default: ")</li>
		 *			<li><b>rowDelimiter</b>: The line ending character (default: auto)</li>
		 *		</ul>
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		public function setOptions(array $options=null){
			$options = ($options === null)? array():$options;
			$this->options = array_merge($this->options_default,$options);
			return $this;
		}
		/**
		 * Sets the options on the parser
		 * @param array $options The options array support certain settings which are highlighted below
		 *		<ul>
		 *			<li><b>delimiter</b>: a single character that serves as the delimiter for content on each line (default: ,)</li>
		 *			<li><b>escape</b>: a single character that's used to escape each field value (default: ")</li>
		 *			<li><b>quote</b>: a single character that's used to enclose each field value (default: ")</li>
		 *			<li><b>rowDelimiter</b>: The line ending character (default: auto)</li>
		 *		</ul>
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		public function setToOptions(array $options=null){
			$options = ($options === null)? array():$options;
			$this->options_to = array_merge($this->options_to_default,$options);
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
			return $this;
		}
		/**
		 * Takes an array of strings and sets it up for parsing
		 * @param array $array The array of strings to be processed
		 * @param array $options
		 * @return \PhpCSV\PhpCSV_Parser
		 * @throws PhpCSV_Parser_Exception
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
			return $this;
		}
		/**
		 * Takes an open file handle and uses the referenced resource as the data source for the parser
		 * @param resource $file_handle A resource obtained by using fopen() in read mode
		 * @param array $options
		 * @return \PhpCSV\PhpCSV_Parser
		 * @throws PhpCSV_Parser_Exception
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
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			if(empty($lines)){
				$this->throwException(
					"Empty CSV file",
					$error_line,
					"No content could be read from the file pointed to by the resource stream"
				);
			}
			$this->setDataArray($lines);
			return $this;
		}
		/**
		 * Sets the data array with the content that will be processed at a later time
		 * @param array $lines An array containing the raw processed data
		 * @return \PhpCSV\PhpCSV_Parser
		 * @throws PhpCSV_Parser_Exception
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
		 * @return \PhpCSV\PhpCSV_Parser
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
				$events = array(
					self::EVENT_ON_CLOSE,self::EVENT_ON_DATA,self::EVENT_ON_END,self::EVENT_ON_ERROR,self::EVENT_ON_RECORD
				);
				if(in_array($event_name, $events)){
					call_user_func_array($handler,$event_data);
				}
			}
			return $this;
		}
		/**
		 * Parses each row of data pulled from the CSV file and converts each row to an array of fields
		 * @return \PhpCSV\PhpCSV_Parser
		 */
		public function parse(){
			$this->parsed_data_array = array();
			foreach($this->data_array as $index=>$csv_string){
				$parsed_row = str_getcsv($csv_string,$this->options['delimiter'],$this->options['quote'],$this->options['escape']);
				$this->fire(self::EVENT_ON_RECORD,array($parsed_row, $index));
				$this->parsed_data_array[] = $parsed_row;
			}
			$this->fire(self::EVENT_ON_END,array(count($this->parsed_data_array)));
			return $this;
		}
		/**
		 * Called after the data has been parsed.
		 * It supplies each parsed row to a user defined function. If the function returns NULL, the index is removed else it
		 * replaces the value at that index with the new value
		 *
		 * @param callable $callable The use defined function to pass each parsed row to
		 * @return \PhpCSV\PhpCSV_Parser
		 * @throws PhpCSV_Parser_Exception
		 */
		public function transform($callable){
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			if(!is_callable($callable)){
				$this->throwException(
					"The specified callable is not Callable",
					$error_line,
					"It could be that the function you supplied to transform could not be found in the context"
				);
			}
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			if(empty($this->parsed_data_array)){
				$this->throwException(
					"Empty data set for transform",
					$error_line,
					"The data has not been parsed so transform should not be called yet!!!"
				);
			}
			foreach($this->parsed_data_array as $index=>$row){
				$transformed_row = call_user_func_array($callable,array($row, $index));
				if($transformed_row === null){
					unset($this->parsed_data_array[$index]);
				} else {
					$this->parsed_data_array[$index] = $transformed_row;
				}
				$this->fire(self::EVENT_ON_DATA,array($transformed_row, $index));
			}
			return $this;
		}
		/**
		 *
		 * @param string $string_param
		 * @param array $options
		 * @return mixed
		 */
		public function to($string_param, array $options=null){
			$this->setToOptions($options);
			$string_param = trim($string_param);
			switch($string_param){
				case "string":
					return $this->to_string();
					break;
				case "array":
					return $this->to_array();
					break;
				default:
					if(is_callable($string_param)){
						#a function to handle the data
						return call_user_func_array($string_param,array($this->parsed_data_array));
					} else if(is_dir(dirname($string_param))){
						#a valid path
						return $this->to_path($string_param);
					} else {
						#i have no idea what it is
						$error_line = __LINE__ - 2;
						$this->throwException(
							"The data destination could not be resolved",
							$error_line,
							"The destination can be any of the following: string, array, a function name (as a string) or
								a path to a file (e.g /home/username/parsed_csv/parsed-content-1.csv and the directory
								/home/username/parsed_csv must exist)"
						);
					}
			}
		}
		/**
		 * Returns the parsed content as a string using the options set in the $options_to array
		 * @return string
		 */
		private function to_string(){
			$csv_string_out = "";
			$line_ending = (strtolower($this->options_to['rowDelimiter']) === "auto")?
				PHP_EOL:$this->options_to['rowDelimiter'];
			foreach($this->parsed_data_array as $row){
				$row_as_string = $this->options_to['quote'].implode(
					"{$this->options_to['quote']}{$this->options_to['delimiter']}{$this->options_to['quote']}",
					$row
				).$this->options_to['quote'].$line_ending;
				$csv_string_out .= $row_as_string;
			}
			return $csv_string_out;
		}
		/**
		 * Returns the processed array of data
		 * @return array
		 */
		private function to_array(){
			return $this->parsed_data_array;
		}
		/**
		 * Writes the parsed content to the specified file
		 * @param string $file_path The path to the file where the data should be written
		 * @return bool
		 */
		private function to_path($file_path){
			$string_content = $this->to_string();
			$error_line = __LINE__ + 2; #WARNING: don't move the line above away from there
			clearstatcache(true, dirname($file_path));
			if(!is_writable(dirname($file_path))){
				#no permissions to the directory
				$this->throwException(
					"The data destination is not writable",
					$error_line,
					"The directory exists but it doesn't seem to be writable by the Apache user and/or group. Please adjust
						the permission settings on your server to allow this!!!"
				);
			}
			$error_line = __LINE__ + 1; #WARNING: don't move the line above away from there
			$file_handle = @fopen($file_path,"wt");
			if($file_handle === false){
				#could not get the file handle
				$this->throwException(
					"A stream could not be created for the data destination",
					$error_line,
					"Please retry the process in a bit..."
				);
			}
			$written = fwrite($file_handle,$string_content);
			fclose($file_handle);
			$this->fire(self::EVENT_ON_CLOSE,array(count($this->parsed_data_array)));
			$is_save_successful = ($written)? true:false;
			return $is_save_successful;
		}
	}
?>