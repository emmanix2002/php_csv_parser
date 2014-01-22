<?php
	namespace Emmanix2002\PhpCSV\Exception;

	use \Exception;

	/**
	 * Exception subclass for the PhpCSV_Parser class.
	 * It contains some additional properties which the base PHP Exception class doesn't contain
	 *
	 * Methods (Getters)
	 * ------------------------------
	 * getDescription() - Returns the description of the error that was thrown..if set
	 * getFilename() - Returns the name of the file where the exception was thrown (should always be PhpCSVParser.php)
	 * getLineNumber() - Returns the line within the file where the error condition occurred
	 * getFullMessage() - Returns a full error response string mixing together all the properties of the class
	 *
	 * @author Emmanuel
	 * @version 1.0.0
	 * @package Emmanuel/php_csv_parser/
	 * @link
	 */
	class PhpCSVParserException extends Exception
    {

		private $error_description;
		private $error_linenum;
		private $error_filename;
		/**
		 * The class constructor for this Exception subclass
		 * @param string $message The main error message
		 * @param string $filename The name of the file where the error occurred
		 * @param string $linenum The line on which the error condition occurred
		 * @param string $description A longer description of the error and probable recommendations
		 */
		public function __construct(
            $message='',
            $filename=null,
            $linenum=null,
            $description=null
        )
        {
			parent::__construct($message, null, null);
			$this->setFilename($filename)
				 ->setDescription($description)
				 ->setLineNumber($linenum);
		}
		/**
		 * Returns the error description
		 * @return string
		 */
		public function getDescription()
        {
			return ($this->error_description)? $this->error_description:'';
		}
		/**
		 * Returns the name of the file where the exception was thrown
		 * @return string
		 */
		public function getFilename()
        {
			return ($this->error_filename)? $this->error_filename:"";
		}
		/**
		 * Returns the line number where the error was triggered in the file
		 * @return int
		 */
		public function getLineNumber()
        {
			return ($this->error_linenum)? $this->error_linenum:"";
		}
		/**
		 * Returns the full error message taking into consideration the context of the error
		 * @return string
		 */
		public function getFullMessage()
        {
			$error_message = sprintf(
				"Error with message '%s (Description: %s)' in file '%s' on line %d",
				parent::getMessage(),
                $this->getDescription(),
                $this->getFilename(),
                $this->getLineNumber()
			);
			return $error_message;
		}
		/**
		 * Sets the value for the description property (i.e. the description of the exception that was thrown)
		 *
         * @param string $description
         *
		 * @return \Emmanix2002\PhpCSV\Exception\PhpCSVParserException
		 */
		public function setDescription($description=null)
        {
			if (is_string($description) and !empty($description))
            {
				$this->error_description = trim($description);
			}
			return $this;
		}
		/**
		 * Sets the value for the filename property (i.e. the filename where the excetion was thrown)
		 * @param string $filename
		 *
         * @return \Emmanix2002\PhpCSV\Exception\PhpCSVParserException
		 */
		public function setFilename($filename=null)
        {
			if (is_string($filename) and !empty($filename))
            {
				$this->error_filename = trim($filename);
			}
			return $this;
		}
		/**
		 * Sets the value for the line number property (i.e. the line where the excetion was thrown)
		 * @param int $linenum
		 *
         * @return \Emmanix2002\PhpCSV\Exception\PhpCSVParserException
		 */
		public function setLineNumber($linenum=null){
			if (is_numeric($linenum))
            {
				$this->error_linenum = trim($linenum);
			}
			return $this;
		}
	}